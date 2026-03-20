<?php if ($statuses !== []): ?>
    <?php foreach ($statuses as $status): ?>
        <article
            class="timeline__item"
            data-status-id="<?= (int) $status['id'] ?>"
            <?php if (session()->get('is_admin')): ?>
            data-status-content="<?= esc($status['content'] ?? '') ?>"
            data-status-media="<?= esc(json_encode(array_map(fn($m) => [
                'id'          => (int) $m['id'],
                'description' => (string) ($m['description'] ?? ''),
                'url'         => (string) ($m['url'] ?? ''),
                'mime_type'   => (string) ($m['mimeType'] ?? $m['mime_type'] ?? ''),
            ], $status['media'] ?? []), JSON_UNESCAPED_SLASHES)) ?>"
            <?php endif; ?>
        >
            <header class="timeline__item-header">
                <img class="timeline__avatar" src="/icon.svg" alt="Profile avatar" width="48" height="48">
                <div class="timeline__identity">
                    <div class="timeline__display-name">Philip Newborough</div>
                    <div class="timeline__handle-and-date">
                        <?php if (! empty($mastodonProfile) && ! empty($mastodonHandle)): ?>
                            <a class="timeline__handle timeline__handle-link" href="<?= esc($mastodonProfile) ?>" target="_blank" rel="noopener noreferrer"><?= esc($mastodonHandle) ?></a>
                        <?php elseif (! empty($mastodonHandle)): ?>
                            <span class="timeline__handle"><?= esc($mastodonHandle) ?></span>
                        <?php else: ?>
                            <span class="timeline__handle">@user</span>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <div class="timeline__content prose">
                <?= $status['content_html'] ?>
            </div>

            <?php if (! empty($status['media'])): ?>
                <div class="timeline__media-grid timeline__media-grid--<?= count($status['media']) > 1 ? 'many' : 'single' ?>">
                    <?php foreach ($status['media'] as $media): ?>
                        <?php $mimeType = (string) ($media['mimeType'] ?? $media['mime_type'] ?? '') ?>
                        <figure class="timeline__media-item">
                            <?php if ($mimeType === 'video/mp4'): ?>
                                <video
                                    class="timeline__media-video"
                                    src="<?= esc($media['url']) ?>"
                                    controls
                                    preload="metadata"
                                    aria-label="<?= esc(! empty($media['description']) ? $media['description'] : 'Status video') ?>"
                                ></video>
                            <?php else: ?>
                                <img
                                    class="timeline__media-image"
                                    src="<?= esc($media['url']) ?>"
                                    alt="<?= esc(! empty($media['description']) ? $media['description'] : 'Status media') ?>"
                                    loading="lazy"
                                    decoding="async"
                                >
                            <?php endif; ?>
                            <?php if (! empty($media['description'])): ?>
                                <figcaption class="timeline__media-caption"><?= esc($media['description']) ?></figcaption>
                            <?php endif; ?>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <footer class="timeline__meta">
                <time class="timeline__time" datetime="<?= esc((string) ($status['created_at'] ?? '')) ?>">
                    <a class="timeline__permalink" href="/status/<?= esc($status['uuid']) ?>"><?= esc(date('j M Y, H:i', strtotime((string) ($status['created_at'] ?? 'now')))) ?></a>
                </time>
                <div class="timeline__item-actions d-flex align-items-center gap-1">
                    <?php if (! empty($status['mastodon_url'])): ?>
                        <a class="timeline__source-link" href="<?= esc($status['mastodon_url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Open original status on Mastodon">
                            <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (session()->get('is_admin')): ?>
                        <button
                            type="button"
                            class="btn btn-sm btn-link timeline__edit-btn p-1 lh-1"
                            data-status-id="<?= (int) $status['id'] ?>"
                            aria-label="Edit status"
                            title="Edit status"
                        ><i class="bi bi-pencil" aria-hidden="true"></i></button>
                        <button
                            type="button"
                            class="btn btn-sm btn-link timeline__delete-btn p-1 lh-1"
                            data-status-id="<?= (int) $status['id'] ?>"
                            aria-label="Delete status"
                            title="Delete status"
                        ><i class="bi bi-trash" aria-hidden="true"></i></button>
                    <?php endif; ?>
                </div>
            </footer>
        </article>
    <?php endforeach; ?>
<?php else: ?>
    <div class="timeline__empty-state">No statuses yet.</div>
<?php endif; ?>
