<?php namespace ProcessWire; ?>
<?php /** @var $items \SeoMaestro\SitemapItem[] */  ?>
<?php echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
<?php foreach ($items as $item): ?>
    <url>
        <loc><?= $item->loc ?></loc>
<?php if ($item->lastmod): ?>
        <lastmod><?= $item->lastmod ?></lastmod>
<?php endif ?>
        <changefreq><?= $item->changefreq ?></changefreq>
        <priority><?= $item->priority ?></priority>
<?php foreach ($item->alternates as $langCode => $url): ?>
        <xhtml:link rel="alternate" hreflang="<?= $langCode ?>" href="<?= $url ?>"/>
<?php endforeach ?>
    </url>
<?php endforeach ?>
</urlset>
