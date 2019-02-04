<?php

namespace SeoMaestro;

use ProcessWire\Language;
use ProcessWire\WireData;
use ProcessWire\WireException;

/**
 * Holds the SEO data of a group such as meta, opengraph, robots or sitemap.
 */
class SeoData extends WireData
{
    /**
     * @var \SeoMaestro\PageValue
     */
    private $pageValue;

    /**
     * @var string
     */
    private $group;

    /**
     * @var \SeoMaestro\SeoDataRendererInterface
     */
    private $renderer;

    /**
     * @param \SeoMaestro\PageValue $pageValue
     * @param string $group
     * @param array $data
     * @param \SeoMaestro\SeoDataRendererInterface $renderer
     */
    public function __construct(PageValue $pageValue, $group, array $data, SeoDataRendererInterface $renderer)
    {
        parent::__construct();

        $this->pageValue = $pageValue;
        $this->group = $group;
        $this->data = $data;
        $this->renderer = $renderer;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $value = $this->lookupUnformattedValue($key);

        return $this->renderer->renderValue($key, $value, $this->pageValue);
    }

    /**
     * Get the rendered value from the field's configuration.
     *
     * @param string $key
     *
     * @return string|null
     */
    public function getInherited($key)
    {
        $value = $this->lookupInheritedValue($key);

        return $this->renderer->renderValue($key, $value, $this->pageValue);
    }

    /**
     * Get the unformatted value of the given key.
     *
     * @param string $key
     *
     * @return string|null
     */
    public function getUnformatted($key) {
        return $this->lookupUnformattedValue($key);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        if (!in_array($key, array_keys($this->data))) {
            throw new WireException(sprintf('Unable to set "%s" for group "%s"', $key, $this->group));
        }

        $langId = $this->getCurrentLanguageId();

        // Propagate the new value back to the page value.
        $keyPageField = sprintf('%s_%s%s', $this->group, $key, $langId);
        $this->pageValue->set($keyPageField, $value);

        if ($this->pageValue->isChanged()) {
            // Notify the page about the change.
            $field = $this->pageValue->getField()->name;
            $this->pageValue->getPage()->trackChange($field);
        }

        return parent::set($key . $langId, $value);
    }

    /**
     * Render all metatags for this group.
     *
     * @return string
     */
    public function ___render()
    {
        // The renderer assumes that inherited data has been looked up.
        $data = [];
        foreach (array_keys($this->data) as $name) {
            // Skip language values.
            if (preg_match('/.*\d{4}$/', $name)) {
                continue;
            }

            $data[$name] = $this->lookupUnformattedValue($name);
        }

        return implode("\n", $this->renderer->renderMetatags($data, $this->pageValue));
    }

    /**
     * Lookup the unformatted value for the given key.
     *
     * If the value is equal to 'inherit', it is looked up from the field's configuration.
     *
     * @param string $key
     *
     * @return string|null
     */
    private function lookupUnformattedValue($key)
    {
        $langId = $this->getCurrentLanguageId();

        // Try to get the value in the current language.
        $value = parent::get($key . $langId);

        // Fallback to default language if no value is set.
        if ($value === null) {
            $value = parent::get($key);
        }

        // Look up inherited value from the field's configuration.
        if ($value === 'inherit') {
            $value = $this->lookupInheritedValue($key);
        }

        return $value;
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    private function lookupInheritedValue($key)
    {
        $langId = $this->getCurrentLanguageId();

        // Get the field in the context of the page's template, config might differ per template.
        $field = $this->pageValue
            ->getPage()
            ->get('template')
            ->get('fieldgroup')
            ->getField($this->pageValue->getField(), true);

        $key = sprintf('%s_%s', $this->group, $key);

        $value = $field->get($key . $langId);

        // Fallback to default language.
        if ($value === null) {
            $value = $field->get($key);
        }

        if ($value !== null) {
            return $value;
        }

        // Fallback to a default config value, if possible, as the default config might not yet exist on the field.
        $defaultConfig = $field->type->getDefaultConfig($field);

        return $defaultConfig[$key] ?? null;
    }

    private function getCurrentLanguageId()
    {
        $currentLanguage = $this->wire('user')->language;

        if (!$currentLanguage instanceof Language) {
            return '';
        }

        return $currentLanguage->isDefault() ? '' : $currentLanguage->id;
    }
}
