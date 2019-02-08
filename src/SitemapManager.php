<?php

namespace SeoMaestro;

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

        $this->data = array_merge(
            [
                'baseUrl' => '',
                'defaultLanguage' => 'en',
            ],
            $config
        );
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
        $pages = $this->getPages();

        if (!$pages->count()) {
            return false;
        }

        $sitemap = $this->renderSitemap($pages);

        return file_put_contents($sitemapPath, $sitemap);
    }

    /**
     * @return \ProcessWire\PageArray
     */
    protected function getPages()
    {
        $templates = $this->getTemplatesWithSeoMaestroField();

        if (!count($templates)) {
            return new PageArray();
        }

        $selector = sprintf('template=%s,template!=admin,id!=%s,include=hidden',
            implode('|', array_keys($templates)),
            $this->getExcludedPages()
        );

        $pages = $this->wire('pages')->findMany($selector);

        $guest = $this->wire('users')->getGuestUser();

        $filtered = [];
        foreach ($pages as $page) {
            $field = $templates[$page->template->name];

            if (!$page->viewable($guest) || !$page->get($field)->sitemap->include) {
                continue;
            }

            // Set a temporary alias to the sitemap data, referenced during rendering.
            $page->set('seoMaestroSitemapData', $page->get($field)->sitemap);

            $filtered[] = $page;
        }

        return (new PageArray())->import($filtered);
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

        // Allow to exclude additional pages by hooking SeoMaestro::sitemapAlwaysExclude().
        return $this->wire('modules')->get('SeoMaestro')
            ->sitemapAlwaysExclude($excluded);
    }

    /**
     * Render the sitemap with the given pages.
     *
     * @param \ProcessWire\PageArray $pages
     *
     * @return string
     */
    private function renderSitemap(PageArray $pages)
    {
        $template = new TemplateFile(dirname(__DIR__) . '/templates/sitemap.xml.php');
        $template->set('pages', $pages);
        $template->set('baseUrl', rtrim($this->get('baseUrl'), '/'));
        $template->set('defaultLanguageCode', $this->get('defaultLanguage'));
        $template->set('hasLanguageSupport', $this->wire('modules')->isInstalled('LanguageSupport'));
        $template->set('hasLanguageSupportPageNames', $this->wire('modules')->isInstalled('LanguageSupportPageNames'));

        // Use the guest user while rendering, to ensure proper page view permissions.
        $user = $this->wire('users')->getCurrentUser();
        $guest = $this->wire('users')->getGuestUser();
        $this->wire('users')->setCurrentUser($guest);

        $sitemap = $template->render();

        $this->wire('users')->setCurrentUser($user);

        return $sitemap;
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
}
