<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
    <channel>
        <title><?= esc($siteName) ?></title>
        <link><?= esc($siteUrl) ?></link>
        <description>Status updates from <?= esc($siteName) ?></description>
        <language>en-gb</language>
        <atom:link href="<?= esc($feedUrl) ?>" rel="self" type="application/rss+xml" />
        <?php foreach ($statuses as $status): ?>
        <?php
            $pubDate  = date(DATE_RSS, strtotime($status['created_at']));
            $guid     = ! empty($status['mastodon_url']) ? $status['mastodon_url'] : $siteUrl . '#status-' . $status['uuid'];
            $link     = ! empty($status['mastodon_url']) ? $status['mastodon_url'] : $siteUrl;
            $content  = ! empty($status['content_html']) ? (string) $status['content_html'] : nl2br(esc((string) $status['content']));
        ?>
        <item>
            <title><?= esc(mb_strimwidth(strip_tags($status['content']), 0, 100, '…')) ?></title>
            <link><?= esc($link) ?></link>
            <guid isPermaLink="<?= ! empty($status['mastodon_url']) ? 'true' : 'false' ?>"><?= esc($guid) ?></guid>
            <pubDate><?= $pubDate ?></pubDate>
            <description><![CDATA[<?= $content ?>]]></description>
            <?php if (! empty($status['media'])): ?>
            <?php foreach ($status['media'] as $i => $media): ?>
            <?php if ($i === 0 && $media['filesize'] > 0): ?>
            <enclosure url="<?= esc($siteUrl . $media['url']) ?>" length="<?= (int) $media['filesize'] ?>" type="<?= esc($media['mimeType']) ?>" />
            <?php endif; ?>
            <media:content url="<?= esc($siteUrl . $media['url']) ?>" type="<?= esc($media['mimeType']) ?>"<?= $media['width'] > 0 ? ' width="' . (int) $media['width'] . '"' : '' ?><?= $media['height'] > 0 ? ' height="' . (int) $media['height'] . '"' : '' ?> medium="image">
                <?php if ($media['description'] !== ''): ?>
                <media:description type="plain"><?= esc($media['description']) ?></media:description>
                <?php endif; ?>
            </media:content>
            <?php endforeach; ?>
            <?php endif; ?>
        </item>
        <?php endforeach; ?>
    </channel>
</rss>
