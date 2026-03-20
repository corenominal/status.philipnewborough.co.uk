<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <!-- Page header -->
    <div class="border-bottom border-1 mb-4 pb-3 d-flex align-items-center justify-content-between gap-3">
        <h2 class="mb-0">Dashboard</h2>
        <a href="/admin" class="btn btn-outline-secondary btn-sm" title="Refresh">
            <i class="bi bi-arrow-clockwise"></i><span class="d-none d-sm-inline"> Refresh</span>
        </a>
    </div>

    <!-- Stat cards: primary row -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 bg-body-secondary">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dashboard__stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-chat-text-fill"></i>
                    </div>
                    <div>
                        <div class="dashboard__stat-value"><?= number_format($totalStatuses) ?></div>
                        <div class="dashboard__stat-label text-secondary">Total Statuses</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 bg-body-secondary">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dashboard__stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div>
                        <div class="dashboard__stat-value"><?= number_format($thisMonth) ?></div>
                        <div class="dashboard__stat-label text-secondary">This Month</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 bg-body-secondary">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dashboard__stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-image-fill"></i>
                    </div>
                    <div>
                        <div class="dashboard__stat-value"><?= number_format($statusesWithMedia) ?></div>
                        <div class="dashboard__stat-label text-secondary">With Media</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 bg-body-secondary">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dashboard__stat-icon dashboard__stat-icon--purple">
                        <i class="bi bi-mastodon"></i>
                    </div>
                    <div>
                        <div class="dashboard__stat-value"><?= number_format($mastodonSynced) ?></div>
                        <div class="dashboard__stat-label text-secondary">Mastodon Synced</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat cards: secondary row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 bg-body-secondary">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dashboard__stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-calendar3"></i>
                    </div>
                    <div>
                        <div class="dashboard__stat-value"><?= number_format($thisYear) ?></div>
                        <div class="dashboard__stat-label text-secondary">This Year</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 bg-body-secondary">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dashboard__stat-icon bg-secondary bg-opacity-25 text-secondary">
                        <i class="bi bi-reply-fill"></i>
                    </div>
                    <div>
                        <div class="dashboard__stat-value"><?= number_format($replies) ?></div>
                        <div class="dashboard__stat-label text-secondary">Replies</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 bg-body-secondary">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dashboard__stat-icon bg-light bg-opacity-10 text-light">
                        <i class="bi bi-text-paragraph"></i>
                    </div>
                    <div>
                        <div class="dashboard__stat-value"><?= number_format($textOnly) ?></div>
                        <div class="dashboard__stat-label text-secondary">Text Only</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 bg-body-secondary">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dashboard__stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-files"></i>
                    </div>
                    <div>
                        <div class="dashboard__stat-value"><?= number_format($totalMedia) ?></div>
                        <div class="dashboard__stat-label text-secondary">Media Files</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity chart + Media breakdown -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 bg-body-secondary h-100">
                <div class="card-header bg-transparent border-bottom border-secondary-subtle">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-bar-chart-fill me-2 text-primary"></i>Activity &mdash; Last 12 Months
                    </h6>
                </div>
                <div class="card-body">
                    <div class="dashboard__activity-chart">
                        <?php foreach ($monthlyData as $month => $count):
                            $heightPct = (int) round(($count / $maxMonthly) * 100);
                        ?>
                        <div class="dashboard__activity-col">
                            <div class="dashboard__activity-count"><?= $count > 0 ? $count : '' ?></div>
                            <div class="dashboard__activity-bar-wrap">
                                <div class="dashboard__activity-bar" style="height:<?= $heightPct ?>%;"></div>
                            </div>
                            <div class="dashboard__activity-label"><?= esc(date('M', strtotime($month . '-01'))) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 bg-body-secondary h-100">
                <div class="card-header bg-transparent border-bottom border-secondary-subtle">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-file-earmark-fill me-2 text-info"></i>Media by Type
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (empty($mediaByType)): ?>
                    <p class="text-secondary small mb-0">No media files yet.</p>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($mediaByType as $mtype):
                            $pct = (int) round(($mtype['cnt'] / $totalMediaForPct) * 100);
                        ?>
                        <div class="list-group-item bg-transparent border-secondary-subtle px-0 py-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold small text-uppercase"><?= esc(strtoupper($mtype['file_ext'])) ?></span>
                                <span class="text-secondary small"><?= number_format($mtype['cnt']) ?> &middot; <?= $pct ?>%</span>
                            </div>
                            <div class="progress" style="height:4px;" role="progressbar"
                                 aria-valuenow="<?= $mtype['cnt'] ?>" aria-valuemin="0" aria-valuemax="<?= $totalMediaForPct ?>">
                                <div class="progress-bar bg-primary" style="width:<?= $pct ?>%;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent statuses -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 bg-body-secondary">
                <div class="card-header bg-transparent border-bottom border-secondary-subtle">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-clock-history me-2 text-warning"></i>Recent Statuses
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentStatuses)): ?>
                    <p class="text-secondary small p-3 mb-0">No statuses yet.</p>
                    <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recentStatuses as $status): ?>
                        <li class="list-group-item bg-transparent border-secondary-subtle px-3 py-3">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div class="flex-grow-1 text-truncate small">
                                    <?= esc(mb_strimwidth(strip_tags($status['content']), 0, 140, '…')) ?>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-shrink-0 text-secondary">
                                    <?php if (!empty($status['media_ids'])): ?>
                                    <i class="bi bi-image-fill text-info" title="Has media"></i>
                                    <?php endif; ?>
                                    <?php if (!empty($status['mastodon_id'])): ?>
                                    <i class="bi bi-mastodon dashboard__mastodon-icon" title="Mastodon synced"></i>
                                    <?php endif; ?>
                                    <span class="small text-nowrap"><?= esc(date('d M Y', strtotime($status['created_at']))) ?></span>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>