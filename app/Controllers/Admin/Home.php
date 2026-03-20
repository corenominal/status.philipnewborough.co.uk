<?php

namespace App\Controllers\Admin;

use App\Models\StatusModel;
use App\Models\MediaModel;

class Home extends BaseController
{
    public function index(): string
    {
        $statusModel = new StatusModel();
        $mediaModel  = new MediaModel();
        $db          = \Config\Database::connect();

        // Headline stat counts
        $totalStatuses     = $statusModel->countAllResults();
        $statusesWithMedia = $statusModel->where("media_ids != '[]'")->countAllResults();
        $textOnly          = $totalStatuses - $statusesWithMedia;
        $mastodonSynced    = $statusModel->where("mastodon_id != ''")->countAllResults();
        $replies           = $statusModel->where("in_reply_to_id != ''")->countAllResults();
        $totalMedia        = $mediaModel->countAllResults();

        // Date-range stats
        $monthStart     = date('Y-m-01');
        $nextMonthStart = date('Y-m-01', strtotime('+1 month'));
        $yearStart      = date('Y-01-01');
        $nextYearStart  = date('Y-01-01', strtotime('+1 year'));

        $thisMonth = $statusModel
            ->where('created_at >=', $monthStart)
            ->where('created_at <', $nextMonthStart)
            ->countAllResults();

        $thisYear = $statusModel
            ->where('created_at >=', $yearStart)
            ->where('created_at <', $nextYearStart)
            ->countAllResults();

        // Monthly activity — last 12 months (current month + 11 prior)
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthlyData[date('Y-m', strtotime("-{$i} months"))] = 0;
        }

        $activityRows = $db->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS cnt
             FROM statuses
             WHERE deleted_at IS NULL
               AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY month
             ORDER BY month ASC"
        )->getResultArray();

        foreach ($activityRows as $row) {
            if (array_key_exists($row['month'], $monthlyData)) {
                $monthlyData[$row['month']] = (int) $row['cnt'];
            }
        }

        $maxMonthly = max(array_values($monthlyData)) ?: 1;

        // Media breakdown by file extension
        $mediaByType = $db->query(
            'SELECT file_ext, COUNT(*) AS cnt FROM media GROUP BY file_ext ORDER BY cnt DESC LIMIT 8'
        )->getResultArray();

        $totalMediaForPct = array_sum(array_column($mediaByType, 'cnt')) ?: 1;

        // Five most recent statuses
        $recentStatuses = $statusModel->orderBy('created_at', 'DESC')->limit(5)->findAll();

        return view('admin/home', [
            'title'             => 'Admin Dashboard',
            'js'                => ['admin/home'],
            'css'               => ['admin/home'],
            'totalStatuses'     => $totalStatuses,
            'statusesWithMedia' => $statusesWithMedia,
            'textOnly'          => $textOnly,
            'mastodonSynced'    => $mastodonSynced,
            'replies'           => $replies,
            'totalMedia'        => $totalMedia,
            'thisMonth'         => $thisMonth,
            'thisYear'          => $thisYear,
            'monthlyData'       => $monthlyData,
            'maxMonthly'        => $maxMonthly,
            'mediaByType'       => $mediaByType,
            'totalMediaForPct'  => $totalMediaForPct,
            'recentStatuses'    => $recentStatuses,
        ]);
    }
}
