document.addEventListener('DOMContentLoaded', () => {
	// -------------------------------------------------------------------------
	// Shared helpers
	// -------------------------------------------------------------------------

	/**
	 * Read a cookie value by name. Returns an empty string when not found.
	 * @param {string} name
	 * @returns {string}
	 */
	const getCookie = (name) => {
		const match = document.cookie.match(new RegExp('(?:^|;\\s*)' + name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '=([^;]*)'));
		return match ? decodeURIComponent(match[1]) : '';
	};

	/**
	 * Build the auth headers needed by every API call.
	 * Returns an empty plain object when no credentials are available.
	 * @returns {Record<string, string>}
	 */
	const authHeaders = () => {
		const userUuid = getCookie('user_uuid');
		const apikey   = getCookie('apikey');

		if (!userUuid || !apikey) {
			return {};
		}

		return { 'user-uuid': userUuid, apikey };
	};

	// -------------------------------------------------------------------------
	// Timeline infinite scroll
	// -------------------------------------------------------------------------

	const timelineItems   = document.querySelector('#timeline-items');
	const observerTarget  = document.querySelector('#timeline-observer');
	const loader          = document.querySelector('#timeline-loader');
	const searchInput     = document.querySelector('#timeline-search');
	const imageModalEl    = document.querySelector('#timeline-image-modal');
	const imageModalImg   = document.querySelector('#timeline-image-modal-img');
	const imageModalCapt  = document.querySelector('#timeline-image-modal-caption');
	const imageModal      = imageModalEl && window.bootstrap
		? window.bootstrap.Modal.getOrCreateInstance(imageModalEl)
		: null;

	if (searchInput) {
		document.addEventListener('keydown', (event) => {
			const isFindShortcut = (event.metaKey || event.ctrlKey)
				&& !event.shiftKey
				&& !event.altKey
				&& event.key.toLowerCase() === 'f';

			if (!isFindShortcut) {
				return;
			}

			event.preventDefault();
			searchInput.focus();
			searchInput.select();
		});
	}

	const initializeImageShimmer = (rootElement) => {
		// Images
		rootElement.querySelectorAll('.timeline__media-image').forEach((image) => {
			if (image.dataset.shimmerInitialized === '1') {
				return;
			}

			image.dataset.shimmerInitialized = '1';

			const mediaItem = image.closest('.timeline__media-item');

			if (!mediaItem) {
				return;
			}

			const markAsLoaded = () => {
				mediaItem.classList.add('has-loaded');
				image.classList.add('is-loaded');
			};

			if (image.complete && image.naturalWidth > 0) {
				markAsLoaded();
				return;
			}

			image.addEventListener('load', markAsLoaded, { once: true });
			image.addEventListener('error', () => {
				mediaItem.classList.add('has-loaded');
			}, { once: true });
		});

		// Videos — resolve the shimmer as soon as metadata is available
		rootElement.querySelectorAll('.timeline__media-video').forEach((video) => {
			if (video.dataset.shimmerInitialized === '1') {
				return;
			}

			video.dataset.shimmerInitialized = '1';

			const mediaItem = video.closest('.timeline__media-item');

			if (!mediaItem) {
				return;
			}

			const markAsLoaded = () => mediaItem.classList.add('has-loaded');

			if (video.readyState >= 1) { // HAVE_METADATA
				markAsLoaded();
				return;
			}

			video.addEventListener('loadedmetadata', markAsLoaded, { once: true });
			video.addEventListener('error', markAsLoaded, { once: true });
		});
	};

	if (timelineItems && observerTarget && loader) {
		if (imageModal && imageModalImg && imageModalCapt) {
			timelineItems.addEventListener('click', (event) => {
				const clickedImage = event.target.closest('.timeline__media-image');

				if (!clickedImage) {
					return;
				}

				event.preventDefault();

				const fullSrc = clickedImage.currentSrc || clickedImage.src;
				const altText = clickedImage.getAttribute('alt') || 'Full size image';

				imageModalImg.src    = fullSrc;
				imageModalImg.alt    = altText;
				imageModalCapt.textContent = altText;

				imageModal.show();
			});

			imageModalEl.addEventListener('hidden.bs.modal', () => {
				imageModalImg.src  = '';
				imageModalImg.alt  = '';
				imageModalCapt.textContent = '';
			});
		}

		const state = {
			isLoading: false,
			offset:    Number(timelineItems.dataset.offset || 0),
			limit:     Number(timelineItems.dataset.limit || 20),
			hasMore:   timelineItems.dataset.hasMore === '1',
			loadUrl:   timelineItems.dataset.loadUrl || '/timeline/load',
			query:     timelineItems.dataset.search || '',
		};

		const setLoaderMessage = (message, className = '') => {
			loader.classList.remove('is-loading', 'is-finished');

			if (className) {
				loader.classList.add(className);
			}

			loader.innerHTML = `<span class="timeline__loader-text">${message}</span>`;
		};

		const loadMoreStatuses = async () => {
			if (state.isLoading || !state.hasMore) {
				return;
			}

			state.isLoading = true;
			setLoaderMessage('Loading more statuses...', 'is-loading');

			try {
				const url = new URL(state.loadUrl, window.location.origin);
				url.searchParams.set('offset', String(state.offset));
				url.searchParams.set('limit', String(state.limit));

				if (state.query.trim() !== '') {
					url.searchParams.set('q', state.query.trim());
				}

				const response = await fetch(url.toString(), {
					method: 'GET',
					headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
				});

				if (!response.ok) {
					throw new Error(`Timeline request failed (${response.status})`);
				}

				const payload = await response.json();

				if (typeof payload.html === 'string' && payload.html.trim() !== '') {
					timelineItems.insertAdjacentHTML('beforeend', payload.html);
					initializeImageShimmer(timelineItems);
				}

				state.offset  = Number(payload.nextOffset || state.offset);
				state.hasMore = Boolean(payload.hasMore);

				if (!state.hasMore) {
					setLoaderMessage('You have reached the end of the timeline.', 'is-finished');
				} else {
					setLoaderMessage('Scroll to load more statuses');
				}
			} catch (error) {
				setLoaderMessage('Unable to load more statuses right now. Try again shortly.');
				// eslint-disable-next-line no-console
				console.error(error);
			} finally {
				state.isLoading = false;
			}
		};

		if (!state.hasMore) {
			setLoaderMessage('You have reached the end of the timeline.', 'is-finished');
			initializeImageShimmer(timelineItems);
		} else {
			initializeImageShimmer(timelineItems);

			const observer = new IntersectionObserver((entries) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting) {
						loadMoreStatuses();
					}
				});
			}, { rootMargin: '500px 0px', threshold: 0 });

			observer.observe(observerTarget);
		}
	}

	// -------------------------------------------------------------------------
	// Compose / edit form (admin only)
	// -------------------------------------------------------------------------

	const composeSection     = document.querySelector('#timeline-compose');

	if (!composeSection) {
		return; // Not admin, nothing more to wire up.
	}

	const composeForm        = document.querySelector('#compose-form');

	// Focus the textarea on page load so the admin can start typing immediately.
	composeSection.querySelector('#compose-content')?.focus();
	const composeStatusIdEl    = document.querySelector('#compose-status-id');
	const composeContentEl     = document.querySelector('#compose-content');
	const composeTitleEl       = document.querySelector('#compose-form-title');
	const composeCancelBtn     = document.querySelector('#compose-cancel-btn');
	const composeSubmitBtn     = document.querySelector('#compose-submit-btn');
	const composeStatusMsg     = document.querySelector('#compose-status-msg');
	const composeAddVideoBtn   = document.querySelector('#compose-add-video-btn');
	const composePendingEl     = document.querySelector('#compose-pending-uploads');
	const composeExistingEl    = document.querySelector('#compose-existing-media');
	const composeExistingList  = document.querySelector('#compose-existing-media-list');
	const composeMastodonWrap  = document.querySelector('#compose-mastodon-wrap');
	const composeMastodonSwitch = document.querySelector('#compose-mastodon-switch');
	const composeCharCount     = document.querySelector('#compose-char-count');

	const CHAR_LIMIT = 500;

	const updateCharCount = () => {
		const remaining = CHAR_LIMIT - composeContentEl.value.length;
		if (composeCharCount) {
			composeCharCount.textContent = remaining;
			composeCharCount.classList.toggle('text-danger', remaining < 0);
			composeCharCount.classList.toggle('text-warning', remaining >= 0 && remaining <= 50);
			composeCharCount.classList.toggle('text-secondary', remaining > 50);
		}
	};

	composeContentEl.addEventListener('input', updateCharCount);

	// Track media state --
	// pendingUploads: array of { el, file, description } — not yet uploaded
	// existingMedia:  array of { id, description, url, mime_type } — from DB
	// removedMediaIds: set of integer IDs to drop on save

	const mediaState = {
		pending:  [],    // { el, file }
		existing: [],    // { id, description, url, mime_type }
		removed:  new Set(),
	};

	// ---- helpers ----

	const setComposeMsg = (msg, type = 'info') => {
		const colours = { info: 'text-secondary', success: 'text-success', error: 'text-danger' };
		composeStatusMsg.className = `timeline-compose__status ms-auto text-end ${colours[type] || ''}`;
		composeStatusMsg.textContent = msg;
	};

	const resetCompose = () => {
		composeStatusIdEl.value    = '0';
		composeContentEl.value     = '';
		composeTitleEl.textContent = 'New Status';
		composeSubmitBtn.textContent = 'Post Status';
		composeCancelBtn.classList.add('d-none');
		setComposeMsg('');
		composePendingEl.innerHTML = '';
		composeExistingList.innerHTML = '';
		composeExistingEl.classList.add('d-none');
		mediaState.pending  = [];
		mediaState.existing = [];
		mediaState.removed.clear();
		updateCharCount();

		if (composeMastodonWrap) {
			composeMastodonWrap.classList.remove('d-none');
		}

		if (composeMastodonSwitch) {
			composeMastodonSwitch.checked = true;
		}
	};

	const setComposeLoading = (isLoading) => {
		composeSubmitBtn.disabled   = isLoading;
		composeAddVideoBtn.disabled = isLoading;
		const label = isLoading
			? (composeStatusIdEl.value !== '0' ? 'Saving…' : 'Posting…')
			: (composeStatusIdEl.value !== '0' ? 'Update Status' : 'Post Status');
		composeSubmitBtn.innerHTML = isLoading
			? `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>${label}`
			: label;
	};

	// ---- existing media items ----

	const buildExistingMediaItem = (media) => {
		const item = document.createElement('div');
		item.className  = 'timeline-compose__existing-item d-flex align-items-center gap-2 mb-2';
		item.dataset.id = String(media.id);

		const isVideo = media.mime_type === 'video/mp4';
		const preview = isVideo
			? `<video class="timeline-compose__thumb" src="${media.url}" muted preload="none"></video>`
			: `<img class="timeline-compose__thumb" src="${media.url}" alt="${media.description || ''}">`;

		item.innerHTML = `
			${preview}
			<span class="flex-grow-1 text-truncate small">${media.description || '<em class="text-secondary">No description</em>'}</span>
			<button type="button" class="btn btn-sm btn-outline-danger timeline-compose__remove-existing" aria-label="Remove media">
				<i class="bi bi-x-lg" aria-hidden="true"></i>
			</button>`;

		item.querySelector('.timeline-compose__remove-existing').addEventListener('click', () => {
			mediaState.removed.add(media.id);
			mediaState.existing = mediaState.existing.filter((m) => m.id !== media.id);
			item.remove();

			if (mediaState.existing.length === 0) {
				composeExistingEl.classList.add('d-none');
			}
		});

		return item;
	};

	const renderExistingMedia = (mediaArray) => {
		composeExistingList.innerHTML = '';

		mediaArray.forEach((m) => {
			composeExistingList.appendChild(buildExistingMediaItem(m));
		});

		if (mediaArray.length > 0) {
			composeExistingEl.classList.remove('d-none');
		} else {
			composeExistingEl.classList.add('d-none');
		}
	};

	// ---- pending upload items ----

	const ALLOWED_MIME_TYPES = new Set(['video/mp4', 'image/jpeg', 'image/png', 'image/gif', 'image/webp']);

	const setFileOnEntry = (entry, file, dropZone, previewEl) => {
		const label = dropZone.querySelector('.timeline-compose__drop-label');

		if (!file || !ALLOWED_MIME_TYPES.has(file.type)) {
			dropZone.classList.add('is-invalid');
			label.textContent = 'Unsupported file type. Allowed: JPEG, PNG, GIF, WebP, MP4.';
			entry.file = null;
			return;
		}

		entry.file = file;
		dropZone.classList.remove('is-invalid');
		dropZone.classList.add('has-file');
		label.textContent = file.name;

		const objectUrl = URL.createObjectURL(file);
		const isVideo   = file.type === 'video/mp4';
		const video     = previewEl.querySelector('.timeline-compose__video-preview');
		const img       = previewEl.querySelector('.timeline-compose__image-preview');

		if (isVideo) {
			video.src = objectUrl;
			video.classList.remove('d-none');
			img.src = '';
			img.classList.add('d-none');
		} else {
			img.src = objectUrl;
			img.classList.remove('d-none');
			video.src = '';
			video.classList.add('d-none');
		}
	};

	const buildPendingUploadItem = () => {
		const wrapper = document.createElement('div');
		wrapper.className = 'timeline-compose__pending-item border rounded p-2 mb-2';

		wrapper.innerHTML = `
			<div class="timeline-compose__drop-zone mb-2" role="button" tabindex="0" aria-label="Drop image or MP4 video here or click to browse">
				<input type="file" accept="image/jpeg,image/png,image/gif,image/webp,video/mp4" class="timeline-compose__drop-input" aria-hidden="true" tabindex="-1">
				<i class="bi bi-paperclip timeline-compose__drop-icon" aria-hidden="true"></i>
				<span class="timeline-compose__drop-label">Drop image or MP4 here, or click to browse</span>
			</div>
			<div class="mb-2">
				<label class="form-label form-label-sm timeline-compose__label mb-1">Description / alt text <span class="text-danger" aria-hidden="true">*</span></label>
				<input type="text" maxlength="255" required class="form-control form-control-sm timeline-compose__desc-input" placeholder="Describe the media (used as alt text)">
			</div>
			<div class="timeline-compose__preview-wrap">
				<img class="timeline-compose__image-preview d-none w-100 rounded mb-2" src="" alt="">
				<video class="timeline-compose__video-preview d-none w-100 rounded mb-2" controls muted preload="none"></video>
			</div>
			<button type="button" class="btn btn-sm btn-outline-danger timeline-compose__remove-pending">Remove</button>`;

		const dropZone  = wrapper.querySelector('.timeline-compose__drop-zone');
		const fileInput = wrapper.querySelector('.timeline-compose__drop-input');
		const previewEl = wrapper.querySelector('.timeline-compose__preview-wrap');
		const descInput = wrapper.querySelector('.timeline-compose__desc-input');
		const removeBtn = wrapper.querySelector('.timeline-compose__remove-pending');

		const entry = { el: wrapper, file: null, descInput };
		mediaState.pending.push(entry);

		// Click / keyboard opens the hidden file input
		dropZone.addEventListener('click', () => fileInput.click());
		dropZone.addEventListener('keydown', (e) => {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				fileInput.click();
			}
		});

		// Drag events
		dropZone.addEventListener('dragover', (e) => {
			e.preventDefault();
			dropZone.classList.add('is-dragover');
		});
		['dragleave', 'dragend'].forEach((evt) => {
			dropZone.addEventListener(evt, () => dropZone.classList.remove('is-dragover'));
		});
		dropZone.addEventListener('drop', (e) => {
			e.preventDefault();
			dropZone.classList.remove('is-dragover');
			const file = e.dataTransfer.files[0];
			setFileOnEntry(entry, file, dropZone, previewEl);
		});

		// Hidden file input change
		fileInput.addEventListener('change', () => {
			setFileOnEntry(entry, fileInput.files[0], dropZone, previewEl);
			fileInput.value = '';
		});

		removeBtn.addEventListener('click', () => {
			mediaState.pending = mediaState.pending.filter((p) => p !== entry);
			wrapper.remove();
		});

		descInput.addEventListener('input', () => {
			if (descInput.value.trim() !== '') {
				descInput.classList.remove('is-invalid');
			}
		});

		return wrapper;
	};

	composeAddVideoBtn.addEventListener('click', () => {
		composePendingEl.appendChild(buildPendingUploadItem());
	});

	// ---- edit button handler (delegated to timeline items container) ----

	if (timelineItems) {
		timelineItems.addEventListener('click', (event) => {
			const editBtn = event.target.closest('.timeline__edit-btn');

			if (!editBtn) {
				return;
			}

			const article  = editBtn.closest('.timeline__item');
			const statusId = editBtn.dataset.statusId;
			const content  = article.dataset.statusContent ?? '';
			let mediaItems = [];

			try {
				mediaItems = JSON.parse(article.dataset.statusMedia || '[]');
			} catch {
				mediaItems = [];
			}

			resetCompose();

			composeStatusIdEl.value      = statusId;
			composeContentEl.value       = content;
			updateCharCount();
			composeTitleEl.textContent   = 'Edit Status';
			composeSubmitBtn.textContent = 'Update Status';
			composeCancelBtn.classList.remove('d-none');

			if (composeMastodonWrap) {
				composeMastodonWrap.classList.add('d-none');
			}

			mediaState.existing = mediaItems.map((m) => ({ ...m }));
			renderExistingMedia(mediaState.existing);

			composeSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
			composeContentEl.focus();
		});
	}

	composeCancelBtn.addEventListener('click', resetCompose);

	// ---- delete button handler ----

	const deleteModalEl      = document.querySelector('#delete-status-modal');
	const deleteConfirmBtn   = document.querySelector('#delete-status-confirm-btn');
	const deleteModal        = deleteModalEl && window.bootstrap
		? window.bootstrap.Modal.getOrCreateInstance(deleteModalEl)
		: null;

	let pendingDeleteId = null;

	if (timelineItems && deleteModal) {
		timelineItems.addEventListener('click', (event) => {
			const deleteBtn = event.target.closest('.timeline__delete-btn');

			if (!deleteBtn) {
				return;
			}

			pendingDeleteId = parseInt(deleteBtn.dataset.statusId, 10);
			deleteModal.show();
		});
	}

	if (deleteConfirmBtn && deleteModal) {
		deleteConfirmBtn.addEventListener('click', async () => {
			if (!pendingDeleteId) {
				return;
			}

			const id = pendingDeleteId;
			pendingDeleteId = null;
			deleteModal.hide();

			try {
				const response = await fetch(`/api/statuses/${id}`, {
					method: 'DELETE',
					headers: { ...authHeaders(), Accept: 'application/json' },
				});

				if (!response.ok) {
					const body = await response.json().catch(() => ({}));
					throw new Error(body.error || `Delete failed (${response.status})`);
				}

				// Remove the article from the DOM
				const article = timelineItems.querySelector(`[data-status-id="${id}"]`);

				if (article) {
					article.remove();
				}

				// If we were editing this status, reset the form
				if (composeStatusIdEl.value === String(id)) {
					resetCompose();
				}
			} catch (error) {
				// eslint-disable-next-line no-alert
				alert(`Could not delete status: ${error.message}`);
			}
		});
	}

	// ---- form submission (create / update) ----

	composeForm.addEventListener('submit', async (event) => {
		event.preventDefault();

		const content  = composeContentEl.value.trim();
		const statusId = parseInt(composeStatusIdEl.value, 10);
		const isEdit   = statusId > 0;

		if (content === '') {
			setComposeMsg('Status content is required.', 'error');
			composeContentEl.focus();
			return;
		}

		if (content.length > CHAR_LIMIT) {
			setComposeMsg(`Status must be ${CHAR_LIMIT} characters or fewer.`, 'error');
			composeContentEl.focus();
			return;
		}

		// Validate pending uploads: each item with a file must have a description.
		for (const entry of mediaState.pending) {
			if (!entry.file) {
				continue;
			}

			const desc = entry.descInput.value.trim();

			if (desc === '') {
				entry.descInput.classList.add('is-invalid');
				entry.descInput.focus();
				setComposeMsg('A description is required for each media item.', 'error');
				return;
			}
		}

		setComposeLoading(true);
		setComposeMsg('');

		try {
			// 1. Upload any pending media files first
			const newMediaIds = [];

			/**
			 * Upload a single file with XHR so we can report progress.
			 * @param {File}    file
			 * @param {string}  description
			 * @param {Element} progressBar   The inner .progress-bar element to update.
			 * @returns {Promise<number>} The new media ID.
			 */
			const uploadWithProgress = (file, description, progressBar) => new Promise((resolve, reject) => {
				const formData = new FormData();
				formData.append('file', file);
				formData.append('description', description);

				const xhr = new XMLHttpRequest();
				const headers = authHeaders();

				xhr.upload.addEventListener('progress', (e) => {
					if (!e.lengthComputable) {
						return;
					}

					const pct = Math.round((e.loaded / e.total) * 100);
					progressBar.style.width = `${pct}%`;
					progressBar.setAttribute('aria-valuenow', String(pct));
					progressBar.textContent = `${pct}%`;
				});

				xhr.addEventListener('load', () => {
					progressBar.style.width = '100%';
					progressBar.setAttribute('aria-valuenow', '100');
					progressBar.textContent = '100%';

					if (xhr.status < 200 || xhr.status >= 300) {
						let msg = `Media upload failed (${xhr.status})`;

						try {
							const body = JSON.parse(xhr.responseText);
							if (body.error) {
								msg = body.error;
							}
						} catch { /* ignore */ }

						progressBar.closest('.progress').classList.add('is-error');
						reject(new Error(msg));
						return;
					}

					try {
						const data = JSON.parse(xhr.responseText);
						resolve(data.data.id);
					} catch {
						reject(new Error('Invalid response from media upload.'));
					}
				});

				xhr.addEventListener('error', () => reject(new Error('Network error during media upload.')));
				xhr.addEventListener('abort', () => reject(new Error('Media upload was cancelled.')));

				xhr.open('POST', '/api/media');
				Object.entries(headers).forEach(([k, v]) => xhr.setRequestHeader(k, v));
				xhr.setRequestHeader('Accept', 'application/json');
				xhr.send(formData);
			});

			for (const entry of mediaState.pending) {
				if (!entry.file) {
					continue;
				}

				// Show progress bar inside the drop zone wrapper
				let progressBar = entry.el.querySelector('.timeline-compose__upload-progress .progress-bar');

				if (!progressBar) {
					const progressWrap = document.createElement('div');
					progressWrap.className = 'progress timeline-compose__upload-progress mb-2';
					progressWrap.setAttribute('role', 'progressbar');
					progressWrap.innerHTML = '<div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">0%</div>';
					entry.el.querySelector('.timeline-compose__remove-pending').before(progressWrap);
					progressBar = progressWrap.querySelector('.progress-bar');
				} else {
					progressBar.style.width = '0%';
					progressBar.setAttribute('aria-valuenow', '0');
					progressBar.textContent = '0%';
					progressBar.closest('.progress').classList.remove('is-error');
				}

				const mediaId = await uploadWithProgress(
					entry.file,
					entry.descInput.value.trim(),
					progressBar,
				);
				newMediaIds.push(mediaId);
			}

			// 2. Build the final media_ids list
			const keptIds     = mediaState.existing
				.filter((m) => !mediaState.removed.has(m.id))
				.map((m) => m.id);
			const allMediaIds = [...keptIds, ...newMediaIds];

			// 3. Create or update the status
			const formData = new FormData();
			formData.append('content', content);
			allMediaIds.forEach((id) => formData.append('media_ids[]', String(id)));

			if (!isEdit && composeMastodonSwitch) {
				formData.append('post_to_mastodon', composeMastodonSwitch.checked ? '1' : '0');
			}

			let statusRes;

			if (isEdit) {				statusRes = await fetch(`/api/statuses/${statusId}`, {
					method: 'PATCH',
					headers: {
						...authHeaders(),
						Accept: 'application/json',
						'Content-Type': 'application/json',
					},
					body: JSON.stringify({ content, media_ids: allMediaIds }),
				});
			} else {
				statusRes = await fetch('/api/statuses', {
					method: 'POST',
					headers: { ...authHeaders(), Accept: 'application/json' },
					body: formData,
				});
			}

			if (!statusRes.ok) {
				const body = await statusRes.json().catch(() => ({}));
				throw new Error(body.error || `Request failed (${statusRes.status})`);
			}

			const statusData = await statusRes.json();

			setComposeMsg(isEdit ? 'Status updated.' : 'Status posted.', 'success');

			if (isEdit) {
				// Update the existing article in the DOM by reloading via timeline/load
				// Simplest approach: reload the page so the updated status reflects.
				// A more sophisticated approach would patch the DOM element, but a reload
				// is reliable and keeps the view consistent.
				window.location.reload();
			} else {
				// Prepend the new status HTML by reloading
				window.location.reload();
			}
		} catch (error) {
			setComposeMsg(error.message, 'error');
			// eslint-disable-next-line no-console
			console.error(error);
		} finally {
			setComposeLoading(false);
		}
	});
});
