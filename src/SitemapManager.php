<?php

namespace SeoMaestro;

use ProcessWire\Page;
use ProcessWire\PageArray;
use ProcessWire\TemplateFile;
use ProcessWire\WireData;

/**
 * Manages the creation of the sitemap.
 */
class SitemapManager extends WireData
{
    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct();

        $this->data = array_merge(['baseUrl' => '', 'defaultLanguage' => 'en'], $config);
    }

    /**
     * Generate the sitemap and store it under the given path.
     *
     * @param string $sitemapPath
     *
     * @return bool|int
     */
    public function generate($sitemapPath)
    {
        $items = $this->buildSitemapItems();

        if (!count($items)) {
            return false;
        }

        $sitemap = $this->renderSitemap($items);

        return file_put_contents($sitemapPath, $sitemap);
    }

    /**
     * @return \SeoMaestro\SitemapItem[]
     */
    private function buildSitemapItems()
    {
        $items = $this->buildSitemapItemsFromPages();

        return $this->wire('modules')->get('SeoMaestro')
            ->sitemapItems($items);
    }

    /**
     * @return \SeoMaestro\SitemapItem[]
     */
    private function buildSitemapItemsFromPages()
    {
        $templates = $this->getTemplatesWithSeoMaestroField();

        if (!count($templates)) {
            return [];
        }

        $selector = sprintf('template=%s,template!=admin,id!=%s,include=hidden',
            implode('|', array_keys($templates)),
            $this->getExcludedPages()
        );

        $pages = $this->wire('pages')->findMany($selector);

        // Use the guest user while building the sitemap items, to ensure proper page view permissions.
        $user = $this->wire('users')->getCurrentUser();
        $guest = $this->wire('users')->getGuestUser();
        $this->wire('users')->setCurrentUser($guest);

        $items = [];
        foreach ($pages as $page) {
            $field = $templates[$page->template->name];

            if (!$page->viewable() || !$page->get($field)->sitemap->include) {
                continue;
            }

            $items = array_merge($items, $this->buildSitemapItemsFromPage($page, $page->get($field)->sitemap));
        }

        $this->wire('users')->setCurrentUser($user);

        return $items;
    }

    /**
     * Return pages that should always be excluded, regardless of sitemap settings on page level.
     *
     * @return \ProcessWire\PageArray
     */
    private function getExcludedPages()
    {
        $page404 = $this->wire('pages')->get($this->wire('config')->http404PageID);

        $excluded = (new PageArray())
            ->add($page404);

        return $this->wire('modules')->get('SeoMaestro')
            ->sitemapAlwaysExclude($excluded);
    }

    /**
     * @return string
     */
    private function renderSitemap(array $items)
    {
        $template = new TemplateFile(dirname(__DIR__) . '/templates/sitemap.xml.php');
        $template->set('items', $items);

        return $template->render();
    }

    /**
     * @return array
     */
    private function getTemplatesWithSeoMaestroField()
    {
        $templates = [];
        foreach ($this->wire('templates') as $template) {
            $fields = $template->fields->find('type=FieldtypeSeoMaestro');
            if (!$fields->count()) {
                continue;
            }

            $templates[$template->name] = $fields->first()->name;
        }

        return $templates;
    }

    /**
     * @return \SeoMaestro\SitemapItem[]
     */
    private function buildSitemapItemsFromPage(Page $page, SitemapSeoData $sitemapData)
    {
        $languageSupport = $this->wire('modules')->isInstalled('LanguageSupport');
        $languageSupportPageNames = $this->wire('modules')->isInstalled('LanguageSupportPageNames');
        $items = [];

        if ($languageSupport && $languageSupportPageNames) {
            foreach ($this->wire('languages') as $language) {
                if (!$page->viewable($language)) {
                    continue;
                }

                $loc = $this->get('baseUrl') ? $this->get('baseUrl') . $page->localUrl($language) : $page->localHttpUrl($language);

                $item = (new SitemapItem())
                    ->set('priority', $sitemapData->priority)
                    ->set('lastmod', $this->getLastMod($page))
                    ->set('changefreq', $sitemapData->changeFrequency)
                    ->set('loc', $loc);

                $this->addAlternatesToSitemapItem($page, $item);
                $items[] = $item;
            }

            return $items;
        }

        // Single language setups.
        $item = (new SitemapItem())
            ->set('priority', $sitemapData->priority)
            ->set('lastmod', $this->getLastMod($page))
            ->set('changefreq', $sitemapData->changeFrequency)
            ->set('loc', $this->get('baseUrl') ? $this->get('baseUrl') . $page->url : $page->httpUrl);

        return [$item];
    }

    private function addAlternatesToSitemapItem(Page $page, SitemapItem $item)
    {
        foreach ($this->wire('languages') as $language) {
            if (!$page->viewable($language)) {
                continue;
            }

            $code = $language->isDefault() ? $this->get('defaultLanguage') : $language->name;
            $loc = $this->get('baseUrl') ? $this->get('baseUrl') . $page->localUrl($language) : $page->localHttpUrl($language);

            $item->addAlternate($code, $loc);
        }
    }

    private function getLastMod(Page $page)
    {
        return date('c', $page->modified);
    }
}
