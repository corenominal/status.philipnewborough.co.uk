<?= $this->extend('templates/default') ?>

<?= $this->section('content') ?>
<section class="gallery-page container-fluid py-4" aria-labelledby="gallery-title">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">

            <header class="gallery-page__header">
                <h1 class="gallery-page__title" id="gallery-title">Media Gallery</h1>
                <p class="gallery-page__subtitle">Images from my posts, served in random order.</p>
                <nav class="gallery-page__subnav" aria-label="Section navigation">
                    <a class="btn btn-sm btn-outline-primary" href="<?= site_url() ?>"><i class="bi bi-clock-history me-1" aria-hidden="true"></i> Timeline</a>
                    <a class="btn btn-sm btn-outline-primary active" href="<?= site_url('gallery') ?>"><i class="bi bi-images me-1" aria-hidden="true"></i> Gallery</a>
                </nav>
            </header>

            <?php if (empty($mediaItems)): ?>
                <p class="text-secondary">No media to display yet.</p>
            <?php else: ?>
                <div
                    class="gallery-grid"
                    id="gallery-grid"
                    aria-label="Media gallery"
                    data-count="<?= count($mediaItems) ?>"
                >
                    <?php foreach ($mediaItems as $item): ?>
                    <a
                        class="gallery-item"
                        href="<?= site_url('status/' . esc($item['status_uuid'])) ?>"
                        aria-label="<?= esc($item['description'] ?: 'View the post that used this image') ?>"
                    >
                        <div
                            class="gallery-item__inner"
                            style="aspect-ratio: <?= (int) $item['width'] ?> / <?= (int) $item['height'] ?>"
                        >
                            <div class="gallery-item__shimmer" aria-hidden="true"></div>
                            <img
                                class="gallery-item__img"
                                data-src="<?= esc($item['url']) ?>"
                                alt="<?= esc($item['description']) ?>"
                                width="<?= (int) $item['width'] ?>"
                                height="<?= (int) $item['height'] ?>"
                            >
                            <div class="gallery-item__overlay" aria-hidden="true">
                                <i class="bi bi-arrow-right-circle-fill gallery-item__overlay-icon"></i>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <p class="gallery-page__count text-secondary mt-4 text-center">
                    <?= count($mediaItems) ?> <?= count($mediaItems) === 1 ? 'image' : 'images' ?>
                </p>
            <?php endif; ?>

        </div>
    </div>
</section>
<?= $this->endSection() ?>
