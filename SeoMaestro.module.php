<?php

namespace ProcessWire;

use SeoMaestro\DatabaseMigrations;
use SeoMaestro\SitemapManager;

/**
 * The main module of SeoMaestro, managing the XML sitemap.
 */
class SeoMaestro extends WireData implements Module, ConfigurableModule
{
    /**
     * @var \SeoMaestro\SitemapManager
     */
    private $sitemapManager;

    public function __construct()
    {
        parent::__construct();

        $this->wire('classLoader')->addNamespace('SeoMaestro', __DIR__ . '/src');
        $this->setDefaultConfig();
    }

    public function init()
    {
        $this->sitemapManager = $this->wire(new SitemapManager($this->getArray()));
    }

    public function ready()
    {
        if ($this->shouldGenerateSitemap()) {
            $this->addHookAfter('ProcessPageView::finished', $this, 'hookGenerateSitemap');
        }
    }

    /**
     * @return \SeoMaestro\SitemapManager
     */
    public function getSitemapManager()
    {
        return $this->sitemapManager;
    }

    /**
     * @param \ProcessWire\HookEvent $event
     */
    public function hookGenerateSitemap(HookEvent $event)
    {
        $sitemap = $this->wire('config')->paths->root . $this->get('sitemapPath');

        if ($this->sitemapManager->generate($sitemap)) {
            $this->wire('session')->message($this->_('The XML sitemap has been generated.'));
        }
    }

    public function ___upgrade($fromVersion, $toVersion)
    {
        $databaseMigrations = $this->wire(new DatabaseMigrations());
        $databaseMigrations->run();
    }

    /**
     * @return array
     */
    public static function getDefaultConfig()
    {
        return [
            'sitemapEnable' => 1,
            'sitemapPath' => 'sitemap.seomaestro.xml',
            'sitemapCache' => 120,
            'baseUrl' => '',
            'defaultLanguage' => 'en',
        ];
    }

    /**
     * Hook to add, remove or modify rendered meta tags of a given group.
     *
     * @param array $tags
     * @param string|null $group
     *
     * @return array
     */
    public function ___renderMetatags(array $tags, $group = null)
    {
        return $tags;
    }

    /**
     * Hook to modify a meta data, after the value has been rendered.
     *
     * @param string $group
     * @param string $name
     * @param mixed $value
     *
     * @return mixed
     */
    public function ___renderSeoDataValue($group, $name, $value)
    {
        return $value;
    }

    /**
     * Hook to alter the form holding the SEO data.
     *
     * Use this hook to customize the inputfields, e.g. change collapsed states
     * or descriptions.
     *
     * @param \ProcessWire\InputfieldWrapper $wrapper
     */
    public function ___alterSeoDataForm(InputfieldWrapper $wrapper)
    {
        return $wrapper;
    }

    /**
     * Hook to modify pages that should always be excluded from the sitemap.
     *
     * Use this hook to add additional pages to the given PageArray. These
     * pages are not rendered in the sitemap, regardless of sitemap settings
     * on page level.
     *
     * @param \ProcessWire\PageArray $excludedPages
     *
     * @return \ProcessWire\PageArray
     */
    public function ___sitemapAlwaysExclude(PageArray $excludedPages)
    {
        return $excludedPages;
    }

    /**
     * Hook to modify sitemap items.
     *
     * Use this hook to add or modify items in the sitemap.
     *
     * @param \SeoMaestro\SitemapItem[] $sitemapItems
     *
     * @return array
     */
    public function ___sitemapItems(array $sitemapItems)
    {
        return $sitemapItems;
    }

    private function shouldGenerateSitemap()
    {
        if (!$this->get('sitemapEnable')) {
            return false;
        }

        if (!$this->get('sitemapPath')) {
            return false;
        }

        // Only create the sitemap if the user is logged into the admin.
        if (!$this->wire('user')->isLoggedin() || $this->wire('page')->template->name !== 'admin') {
            return false;
        }

        $sitemap = $this->wire('config')->paths->root . ltrim($this->get('sitemapPath'), DIRECTORY_SEPARATOR);

        // Do not generate if cache is still valid.
        if (is_file($sitemap)) {
            $diffMinutes = (time() - filemtime($sitemap)) / 60;

            if ($diffMinutes < $this->get('sitemapCache')) {
                return false;
            }
        }

        return true;
    }

    private function setDefaultConfig()
    {
        $this->setArray(self::getDefaultConfig());
    }

    /**
     * @param array $data
     *
     * @return \ProcessWire\InputfieldWrapper
     */
    public static function getModuleConfigInputfields(array $data)
    {
        $wrapper = new InputfieldWrapper();
        $data = array_merge(self::getDefaultConfig(), $data);

        $field = wire('modules')->get('InputfieldCheckbox');
        $field->label = __('Enable sitemap generation');
        $field->description = __('Check to let the module manage the sitemap.');
        $field->attr('name', 'sitemapEnable');
        $field->attr('checked', (bool)$data['sitemapEnable']);
        $wrapper->append($field);

        $field = wire('modules')->get('InputfieldText');
        $field->label = __('Sitemap path');
        $field->description = __('Path and filename of the sitemap relative from the ProcessWire root directory.');
        $field->attr('name', 'sitemapPath');
        $field->attr('value', $data['sitemapPath']);
        $field->showIf = 'sitemapEnable=1';
        $wrapper->append($field);

        $field = wire('modules')->get('InputfieldText');
        $field->label = __('Cache time');
        $field->description = __('How long should the sitemap be cached? Enter a time in minutes or `0` to disable caching (not recommended, unless during development or to immediately generate the sitemap after saving).');
        $field->attr('name', 'sitemapCache');
        $field->attr('value', $data['sitemapCache']);
        $field->showIf = 'sitemapEnable=1';
        $wrapper->append($field);

        $field = wire('modules')->get('InputfieldText');
        $field->label = __('Base url');
        $field->description = __('The base url used for links in the sitemap, e.g. `https://yourdomain.com`. If empty, the current domain is used.');
        $field->attr('name', 'baseUrl');
        $field->attr('value', $data['baseUrl']);
        $wrapper->append($field);

        $field = wire('modules')->get('InputfieldText');
        $field->label = __('Default language');
        $field->description = __('2-letter language code of ProcessWire\'s default language.');
        $field->attr('name', 'defaultLanguage');
        $field->attr('value', $data['defaultLanguage']);
        $wrapper->append($field);

        return $wrapper;
    }
}
