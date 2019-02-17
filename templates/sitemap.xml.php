<?php namespace ProcessWire; ?>
<?php /** @var $pages \ProcessWire\PageArray */  ?>
<?php /** @var $languages \ProcessWire\PageArray */  ?>
<?php echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
<?php foreach ($pages as $page): ?>
<?php if ($hasLanguageSupport && $hasLanguageSupportPageNames): ?>
<?php foreach ($languages ?: [] as $language): ?>
<?php if (!$page->viewable($language)) continue; ?>
    <url>
        <loc><?= $baseUrl ? $baseUrl . $page->localUrl($language) : $page->localHttpUrl($language) ?></loc>
        <lastmod><?= date('c', $page->modified) ?></lastmod>
        <changefreq><?= $page->seoMaestroSitemapData->changeFrequency ?></changefreq>
        <priority><?= $page->seoMaestroSitemapData->priority ?></priority>
<?php foreach ($languages as $language): ?>
<?php if (!$page->viewable($language)) continue; ?>
        <xhtml:link rel="alternate" hreflang="<?= $language->isDefault() ? $defaultLanguageCode : $language->name ?>" href="<?= $baseUrl ? $baseUrl . $page->localUrl($language) : $page->localHttpUrl($language) ?>"/>
<?php endforeach ?>
    </url>
<?php endforeach ?>
<?php else: ?>
    <url>
        <loc><?= $baseUrl ? $baseUrl . $page->url : $page->httpUrl ?></loc>
        <lastmod><?= $page->modifiedStr ?></lastmod>
        <changefreq><?= $page->seoMaestroSitemapData->changeFrequency ?></changefreq>
        <priority><?= $page->seoMaestroSitemapData->priority ?></priority>
    </url>
<?php endif ?>
<?php endforeach ?>
</urlset>
