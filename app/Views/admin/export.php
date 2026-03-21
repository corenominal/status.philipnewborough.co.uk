<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <!-- Page header -->
    <div class="border-bottom border-1 mb-4 pb-3 d-flex align-items-center justify-content-between gap-3">
        <h2 class="mb-0">Export Data</h2>
        <span class="badge bg-secondary fs-6"><?= number_format($totalStatuses) ?> statuses</span>
    </div>

    <div class="row g-4">

        <!-- JSON Export -->
        <div class="col-12 col-lg-4">
            <div class="card h-100 border-0 bg-body-secondary export__card">
                <div class="card-body d-flex flex-column gap-3 p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="export__icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-filetype-json"></i>
                        </div>
                        <div>
                            <h3 class="h5 mb-0">JSON Export</h3>
                            <span class="badge bg-primary bg-opacity-25 text-primary-emphasis mt-1">Full data</span>
                        </div>
                    </div>
                    <p class="text-secondary mb-0">
                        Exports all statuses as a structured JSON file. Includes every field — content, HTML, media IDs, Mastodon metadata, and timestamps. Ideal for backups or importing into another system.
                    </p>
                    <div class="mt-auto">
                        <a href="/admin/export/json" class="btn btn-primary w-100 export__btn" data-format="json">
                            <i class="bi bi-download me-2"></i>Download JSON
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SQL Export -->
        <div class="col-12 col-lg-4">
            <div class="card h-100 border-0 bg-body-secondary export__card">
                <div class="card-body d-flex flex-column gap-3 p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="export__icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-database"></i>
                        </div>
                        <div>
                            <h3 class="h5 mb-0">SQL Export</h3>
                            <span class="badge bg-warning bg-opacity-25 text-warning-emphasis mt-1">Database dump</span>
                        </div>
                    </div>
                    <p class="text-secondary mb-0">
                        Generates a SQL dump containing both a <code>CREATE TABLE</code> statement and <code>INSERT</code> rows for all statuses. Use this to restore or migrate your data into any MySQL-compatible database.
                    </p>
                    <div class="mt-auto">
                        <a href="/admin/export/sql" class="btn btn-warning w-100 export__btn" data-format="sql">
                            <i class="bi bi-download me-2"></i>Download SQL
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Analysis Export -->
        <div class="col-12 col-lg-4">
            <div class="card h-100 border-0 bg-body-secondary export__card">
                <div class="card-body d-flex flex-column gap-3 p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="export__icon export__icon--purple">
                            <i class="bi bi-stars"></i>
                        </div>
                        <div>
                            <h3 class="h5 mb-0">AI Analysis</h3>
                            <span class="badge export__badge--purple mt-1">Writing style</span>
                        </div>
                    </div>
                    <p class="text-secondary mb-0">
                        Exports all status text — stripped of HTML — as a plain-text file, prefixed with a prompt ready to paste into an AI assistant. Use it to generate a personal writing style guide.
                    </p>

                    <div class="export__prompt-block p-3 rounded">
                        <p class="export__prompt-label text-uppercase fw-semibold mb-2">
                            <i class="bi bi-chat-square-quote me-1"></i>Prompt included in file
                        </p>
                        <p class="export__prompt-text mb-0">
                            Analyze the following messages for my personal writing style. Look at sentence structure, level of formality, use of emojis, punctuation habits, and common vocabulary. Create a concise 'Style Guide' I can use for future prompts.
                        </p>
                        <button class="btn btn-sm export__copy-btn mt-2" id="copyPromptBtn" type="button">
                            <i class="bi bi-clipboard me-1"></i>Copy prompt
                        </button>
                    </div>

                    <div class="mt-auto">
                        <a href="/admin/export/ai" class="btn w-100 export__btn export__btn--purple" data-format="ai">
                            <i class="bi bi-download me-2"></i>Download for AI
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>
