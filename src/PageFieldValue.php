<?php

namespace SeoMaestro;

use ProcessWire\Field;
use ProcessWire\Page;
use ProcessWire\SeoMaestro;
use ProcessWire\WireData;
use ProcessWire\WireException;

/**
 * The page field value returned by fields of type "FieldtypeSeoMaestro".
 */
class PageFieldValue extends WireData
{
    /**
     * @var \ProcessWire\Page
     */
    private $page;

    /**
     * @var \ProcessWire\Field
     */
    private $field;

    /**
     * @var \Processwire\SeoMaestro
     */
    private $seoMaestro;

    /**
     * @var array
     */
    private $seoData = [];

    /**
     * @param \ProcessWire\Page $page
     * @param \ProcessWire\Field $field
     * @param \ProcessWire\SeoMaestro $seoMaestro
     * @param array $data
     */
    public function __construct(Page $page, Field $field, SeoMaestro $seoMaestro, array $data = [])
    {
        parent::__construct();

        $this->page = $page;
        $this->field = $field;
        $this->seoMaestro = $seoMaestro;
        $this->setDefaultData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        if ($key === 'og') {
            return $this->getSeoData('opengraph');
        }

        if ($this->isSeoGroup($key)) {
            return $this->getSeoData($key);
        }

        return parent::get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        static $regex = null;

        // Make sure to only allow keys of valid seo data, e.g. meta_title.
        if ($regex === null) {
            $seoFields = implode('|', array_keys($this->field->type->getSeoData()));
            $languageIds = $this->wire('languages') ? $this->wire('languages')->getAll()->implode('|', 'id') : '';
            $regex = sprintf('/^(%s)(%s){0,1}$/', $seoFields, $languageIds);
        }

        if (!preg_match($regex, $key)) {
            throw new WireException(sprintf('"%s" is not a valid key for a SeoMaestro page value', $key));
        }

        return parent::set($key, $value);
    }

    /**
     * Render meta tags of all groups.
     *
     * @return string
     */
    public function render()
    {
        $tags = array_map(function ($group) {
            return $this->getSeoData($group)->render();
        }, $this->field->type->getSeoGroups());

        $tags += $this->seoMaestro->renderMetatags($this->getCommonMetatags());

        $tags = array_filter($tags, function ($tag) {
            return $tag !== '';
        });

        return implode("\n", $tags);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * @return \ProcessWire\Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return \ProcessWire\Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Build common metatags not configured via fieldtype.
     *
     * @return array
     */
    private function getCommonMetatags()
    {
        $baseUrl = $this->seoMaestro->get('baseUrl');
        $defaultLang = $this->seoMaestro->get('defaultLanguage') ?: 'en';
        $hasLanguageSupportPageNames = $this->wire('modules')->isInstalled('LanguageSupportPageNames');

        $tags = ['meta_generator' => '<meta name="generator" content="ProcessWire">'];

        if ($hasLanguageSupportPageNames) {
            foreach ($this->wire('languages') ?: [] as $language) {
                if (!$this->page->viewable($language)) {
                    continue;
                }

                $code = $language->isDefault() ? $defaultLang : $language->name;
                $url = $baseUrl ? $baseUrl . $this->page->localUrl($language) : $this->page->localHttpUrl($language);

                $tags["link_rel_{$code}"] = sprintf('<link rel="alternate" href="%s" hreflang="%s">', $url, $code);

                if ($language->isDefault()) {
                    $tags['link_rel_default'] = sprintf('<link rel="alternate" href="%s" hreflang="x-default">', $url);
                }
            }
        }

        return $tags;
    }

    private function isSeoGroup($name)
    {
        return in_array($name, $this->field->type->getSeoGroups());
    }

    /**
     * @param string $group
     *
     * @return \SeoMaestro\SeoDataInterface
     */
    private function getSeoData($group)
    {
        if (!isset($this->seoData[$group])) {
            $class = sprintf('\\SeoMaestro\\%sSeoData', ucfirst($group));
            $data = $this->getDataOfGroup($group);
            $seoData = $this->wire(new $class($this, $this->seoMaestro, $data));
            $this->seoData[$group] = $seoData;
        }

        return $this->seoData[$group];
    }

    /**
     * @param string $group
     *
     * @return array
     */
    private function getDataOfGroup($group)
    {
        $data = [];

        foreach ($this->data as $key => $value) {
            list($_group, $name) = explode('_', $key);
            if ($_group === $group) {
                $data[$name] = $value;
            }
        }

        return $data;
    }

    private function setDefaultData(array $data)
    {
        $defaults = [];
        foreach (array_keys($this->field->type->getSeoData()) as $name) {
            $defaults[$name] = 'inherit';
        }

        $this->data = array_merge($defaults, $data);
    }
}
