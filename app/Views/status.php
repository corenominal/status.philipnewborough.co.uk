<?= $this->extend('templates/default') ?>

<?= $this->section('content') ?>
<section class="container py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="mb-3">
                <a href="<?= esc($backUrl) ?>" class="text-decoration-none text-secondary small">&larr; <?= esc($backLabel) ?></a>
            </div>
            <div class="timeline" id="timeline-items">
                <?= view('home/partials/timeline_items', [
                    'statuses'        => [$status],
                    'mastodonHandle'  => $mastodonHandle,
                    'mastodonProfile' => $mastodonProfile,
                ]) ?>
            </div>

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
        </div>
    </div>
</section>

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

<?= $this->endSection() ?>
