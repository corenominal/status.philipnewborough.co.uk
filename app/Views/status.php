<?= $this->extend('templates/default') ?>

<?= $this->section('content') ?>
<section class="container py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="mb-3">
                <a href="/" class="text-decoration-none text-secondary small">&larr; Back to timeline</a>
            </div>
            <div class="timeline" id="timeline-items">
                <?= view('home/partials/timeline_items', [
                    'statuses'        => [$status],
                    'mastodonHandle'  => $mastodonHandle,
                    'mastodonProfile' => $mastodonProfile,
                ]) ?>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
