<?= $this->extend('templates/default') ?>

<?= $this->section('content') ?>
<section class="about-page container py-4" aria-labelledby="about-title">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">

            <header class="about-page__header">
                <h1 class="about-page__title" id="about-title">About</h1>
                <nav class="about-page__subnav" aria-label="Section navigation">
                    <a class="btn btn-sm btn-outline-primary" href="<?= site_url() ?>"><i class="bi bi-clock-history me-1" aria-hidden="true"></i> Timeline</a>
                    <a class="btn btn-sm btn-outline-primary" href="<?= site_url('gallery') ?>"><i class="bi bi-images me-1" aria-hidden="true"></i> Gallery</a>
                    <a class="btn btn-sm btn-outline-primary active" href="<?= site_url('about') ?>"><i class="bi bi-info-circle me-1" aria-hidden="true"></i> About</a>
                </nav>
            </header>

            <div class="about-page__content mt-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="about-page__card">
                            <h2 class="h5 mb-3">What is this?</h2>
                            <p>This is the personal status timeline of <strong><?= esc(config('Personal')->name) ?></strong>. It's a space for short-form posts - thoughts, links, images, and other snippets shared as they happen.</p>
                            <p class="mb-4">Posts are also syndicated to the Fediverse via Mastodon, so you can follow along there if that's your preferred platform.</p>

                            <h2 class="h5 mb-3">What kinds of posts can I expect?</h2>
                            <ul class="list-unstyled d-flex flex-column gap-2 mb-4">
                                <li><i class="bi bi-chat-left-text-fill me-2 text-primary" aria-hidden="true"></i> Short text updates and observations</li>
                                <li><i class="bi bi-link-45deg me-2 text-primary" aria-hidden="true"></i> Links to articles, projects, and things of interest</li>
                                <li><i class="bi bi-image-fill me-2 text-primary" aria-hidden="true"></i> Photos and images</li>
                                <li><i class="bi bi-code-slash me-2 text-primary" aria-hidden="true"></i> Tech notes and code snippets</li>
                            </ul>

                            <h2 class="h5 mb-3">Stay connected</h2>
                            <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                                <li>
                                    <a class="text-decoration-none" href="<?= site_url('feed/rss') ?>">
                                        <i class="bi bi-rss-fill me-2 text-warning" aria-hidden="true"></i> Subscribe via RSS feed
                                    </a>
                                </li>
                                <?php if (config('Mastodon')->profile): ?>
                                <li>
                                    <a class="text-decoration-none" href="<?= esc(config('Mastodon')->profile) ?>" rel="me noopener noreferrer" target="_blank">
                                        <i class="bi bi-mastodon me-2 text-primary" aria-hidden="true"></i> Follow on Mastodon
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<?= $this->endSection() ?>
