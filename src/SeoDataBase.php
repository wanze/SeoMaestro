<?php

namespace SeoMaestro;

use ProcessWire\Language;
use ProcessWire\WireData;
use ProcessWire\WireException;

/**
 * Base class for each group holding a bunch of SEO data.
 */
abstract class SeoDataBase extends WireData implements SeoDataInterface
{
    /**
     * @var \SeoMaestro\PageValue
     */
    protected $pageValue;

    /**
     * @var string
     */
    protected $group;

    /**
     * @param \SeoMaestro\PageValue $pageValue
     * @param array $data
     */
    public function __construct(PageValue $pageValue, array $data)
    {
        parent::__construct();

        $this->pageValue = $pageValue;
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        $value = $this->lookupUnformattedValue($name);

        return $this->renderValue($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getInherited($name)
    {
        $value = $this->lookupInheritedValue($name);

        return $this->renderValue($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnformatted($name) {
        return $this->lookupUnformattedValue($name);
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        if (!in_array($name, array_keys($this->data))) {
            throw new WireException(sprintf('Unable to set "%s" for group "%s"', $name, $this->group));
        }

        $value = $this->sanitizeValue($name, $value);

        // Strip tags and remove newlines.
        $value = $this->wire('sanitizer')->text($value, ['maxLength' => 0]);

        $langId = $this->getCurrentLanguageId();

        // Propagate the new value back to the page value.
        $keyPageField = sprintf('%s_%s%s', $this->group, $name, $langId);
        $this->pageValue->set($keyPageField, $value);

        if ($this->pageValue->isChanged()) {
            // Notify the page about the change.
            $field = $this->pageValue->getField()->name;
            $this->pageValue->getPage()->trackChange($field);
        }

        return parent::set($name . $langId, $value);
    }

    /**
     * Render all metatags for this group.
     *
     * @return string
     */
    public function ___render()
    {
        $data = [];
        foreach (array_keys($this->data) as $name) {
            // Skip language values.
            if (preg_match('/.*\d{4}$/', $name)) {
                continue;
            }

            $data[$name] = $this->lookupUnformattedValue($name);
        }

        return implode("\n", $this->renderMetatags($data));
    }

    /**
     * Render an unformatted value, e.g. transform placeholders to actual values.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return string
     */
    abstract protected function renderValue($name, $value);

    /**
     * Return the rendered meta tags of the given data as array.
     *
     * @param array $data
     *
     * @return array
     */
    abstract protected function ___renderMetatags(array $data);

    /**
     * Sanitize the given unformatted value of the given name.
     *
     * Throw an exception if the value is not valid.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return mixed
     */
    abstract protected function sanitizeValue($name, $value);

    /**
     * Lookup the unformatted value for the given key.
     *
     * If the value is equal to 'inherit', it is looked up from the field's configuration.
     *
     * @param string $key
     *
     * @return string|null
     */
    protected function lookupUnformattedValue($key)
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
    protected function lookupInheritedValue($key)
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

    /**
     * Encode the value to be used in a meta tag.
     *
     * First strips HTML tags and newlines, then encode any entities.
     *
     * @param string $value
     *
     * @return string
     */
    protected function encode($value)
    {
        $sanitizer = $this->wire('sanitizer');

        return $sanitizer->entities1(
            $sanitizer->text($value, ['maxLength' => 0])
        );
    }

    protected function containsPlaceholder($value)
    {
        return preg_match('/\{.*\}/', $value);
    }

    protected function getCurrentLanguageId()
    {
        $currentLanguage = $this->wire('user')->language;

        if (!$currentLanguage instanceof Language) {
            return '';
        }

        return $currentLanguage->isDefault() ? '' : $currentLanguage->id;
    }
}
