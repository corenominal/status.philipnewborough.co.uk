<?= $this->extend('templates/default') ?>

<?= $this->section('content') ?>
<section class="timeline-page container py-3" data-batch-size="<?= (int) $statusBatchSize ?>">
    <div class="row g-4 justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="timeline-page__header">
                <div class="timeline-page__title-row">
                    <h1 class="timeline-page__title mb-0">Status Timeline</h1>
                    <form class="timeline-page__search" method="get" action="/" role="search" aria-label="Search statuses">
                        <label for="timeline-search" class="visually-hidden">Search statuses</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                            <input
                                type="search"
                                id="timeline-search"
                                name="q"
                                class="form-control"
                                placeholder="Search statuses"
                                value="<?= esc($searchQuery) ?>"
                                autocomplete="off"
                            >
                        </div>
                    </form>
                </div>
                <p class="timeline-page__subtitle mb-0">Recent updates, Mastodon style.</p>
            </div>

            <?php if (session()->get('is_admin')): ?>
                <section class="timeline-compose mb-4" aria-label="Create or edit status" id="timeline-compose">
                    <header class="timeline-compose__header d-flex align-items-center justify-content-between mb-3">
                        <h2 class="timeline-compose__title mb-0" id="compose-form-title">New Status</h2>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary d-none"
                            id="compose-cancel-btn"
                        >Cancel edit</button>
                    </header>
                    <form class="timeline-compose__form" id="compose-form" novalidate>
                        <input type="hidden" id="compose-status-id" value="0">
                        <div class="mb-3">
                            <label class="form-label timeline-compose__label" for="compose-content">Status content</label>
                            <textarea
                                class="form-control timeline-compose__textarea"
                                id="compose-content"
                                name="content"
                                rows="4"
                                placeholder="What's happening?"
                                maxlength="500"
                            ></textarea>
                            <div class="d-flex justify-content-end mt-1">
                                <span id="compose-char-count" class="small text-secondary">500</span>
                            </div>
                        </div>

                        <div class="timeline-compose__existing-media d-none mb-3" id="compose-existing-media">
                            <p class="form-label timeline-compose__label mb-2">Attached media</p>
                            <div class="timeline-compose__existing-media-list" id="compose-existing-media-list"></div>
                        </div>

                        <div id="compose-pending-uploads"></div>

                        <div class="d-flex gap-2 align-items-center flex-wrap mt-2">
                            <button type="submit" class="btn btn-primary" id="compose-submit-btn">Post Status</button>
                            <button type="button" class="btn btn-outline-secondary" id="compose-add-video-btn">
                                <i class="bi bi-paperclip me-1" aria-hidden="true"></i>Add media
                            </button>
                            <?php if ($mastodonEnabled): ?>
                                <div class="form-check form-switch ms-1" id="compose-mastodon-wrap">
                                    <input class="form-check-input" type="checkbox" role="switch" id="compose-mastodon-switch" checked>
                                    <label class="form-check-label text-secondary small" for="compose-mastodon-switch">Post to Mastodon</label>
                                </div>
                            <?php endif; ?>
                            <span class="timeline-compose__status ms-auto text-end" id="compose-status-msg" aria-live="polite"></span>
                        </div>
                    </form>
                </section>
            <?php endif; ?>

            <section class="timeline" aria-label="Status updates">
                <div
                    class="timeline__items"
                    id="timeline-items"
                    data-load-url="/timeline/load"
                    data-offset="<?= count($statuses) ?>"
                    data-limit="<?= (int) $statusBatchSize ?>"
                    data-has-more="<?= $hasMoreStatuses ? '1' : '0' ?>"
                    data-search="<?= esc($searchQuery) ?>"
                >
                    <?= view('home/partials/timeline_items', [
                        'statuses' => $statuses,
                        'mastodonHandle' => $mastodonHandle,
                        'mastodonProfile' => $mastodonProfile,
                    ]) ?>
                </div>

                <div id="timeline-loader" class="timeline__loader" aria-live="polite">
                    <span class="timeline__loader-text">Scroll to load more statuses</span>
                </div>

                <div id="timeline-observer" class="timeline__observer" aria-hidden="true"></div>
            </section>

            <div class="modal fade timeline-image-modal" id="timeline-image-modal" tabindex="-1" aria-labelledby="timeline-image-modal-label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content timeline-image-modal__content">
                        <div class="modal-header border-0 pb-0">
                            <h2 class="modal-title fs-6" id="timeline-image-modal-label">Image Preview</h2>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-2">
                            <img id="timeline-image-modal-img" class="timeline-image-modal__image" src="" alt="">
                            <p id="timeline-image-modal-caption" class="timeline-image-modal__caption mb-0"></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (session()->get('is_admin')): ?>
            <div class="modal fade" id="delete-status-modal" tabindex="-1" aria-labelledby="delete-status-modal-label" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title fs-5" id="delete-status-modal-label">Delete status</h2>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">Are you sure you want to delete this status? This cannot be undone.</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="delete-status-confirm-btn">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?= $this->endSection() ?>