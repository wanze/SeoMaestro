<?php

namespace SeoMaestro;

use ProcessWire\Field;
use ProcessWire\Page;
use ProcessWire\WireData;
use ProcessWire\WireException;

/**
 * The page field value returned by fields of type "FieldtypeSeoMaestro".
 */
class PageValue extends WireData
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
     * @var array
     */
    private $seoData = [];

    /**
     * @param \ProcessWire\Page $page
     * @param \ProcessWire\Field $field
     * @param array $data
     */
    public function __construct(Page $page, Field $field, array $data = [])
    {
        parent::__construct();

        $this->page = $page;
        $this->field = $field;
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
    public function ___render()
    {
        $tags = array_map(function ($group) {
            return $this->getSeoData($group)->render();
        }, $this->field->type->getSeoGroups());

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
            $seoData = $this->wire(new $class($this, $data));
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
