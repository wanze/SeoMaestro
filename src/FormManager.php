<?php

namespace SeoMaestro;

use ProcessWire\Inputfield;
use ProcessWire\InputfieldText;
use ProcessWire\InputfieldWrapper;
use ProcessWire\SeoMaestro;
use ProcessWire\Wire;

/**
 * A manager to build and populate a form containing SEO data.
 */
class FormManager extends Wire
{
    /**
     * @var \ProcessWire\SeoMaestro
     */
    private $seoMaestro;

    /**
     * @param \ProcessWire\SeoMaestro $seoMaestro
     */
    public function __construct(SeoMaestro $seoMaestro)
    {
        parent::__construct();

        $this->seoMaestro = $seoMaestro;
    }

    /**
     * Build the form containing all inputfields of the given SEO data.
     *
     * @param array $seoData
     *
     * @return InputfieldWrapper
     */
    public function buildForm(array $seoData)
    {
        $wrapper = $this->wire(new InputfieldWrapper());

        foreach ($seoData as $key => $options) {
            list($group, $name) = explode('_', $key);
            $groupName = 'group_' . $group;

            $fieldset = $wrapper->get($groupName);
            if (!$fieldset) {
                $fieldset = $this->wire('modules')->get('InputfieldFieldset');
                $fieldset->label = $this->_(ucfirst($group));
                $fieldset->collapsed = ($group === 'meta') ? Inputfield::collapsedNo : Inputfield::collapsedYes;
                $fieldset->attr('name', $groupName);
                $wrapper->append($fieldset);
            }

            $inputfield = $this->getInputfield($group, $name, $options);
            $fieldset->append($inputfield);
        }

        return $this->seoMaestro->alterSeoDataForm($wrapper);
    }

    /**
     * Populate the given form with the given data.
     *
     * @param \ProcessWire\InputfieldWrapper $form
     * @param array $values
     */
    public function populateValues(InputfieldWrapper $form, array $values)
    {
        $form->populateValues($values);

        foreach ($form->getAll() as $inputfield) {
            if ($inputfield->useLanguages) {
                $this->populateLanguageValue($inputfield, $values);
            }
        }
    }

    /**
     * Populate values of all languages to the given inputfield.
     *
     * @param \ProcessWire\Inputfield $inputfield
     * @param array $values
     */
    private function populateLanguageValue(Inputfield $inputfield, array $values)
    {
        foreach ($this->wire('languages') ?: [] as $language) {
            $langId = $language->id;
            $name = $inputfield->attr('name');
            $value = $values[$name . $langId] ?? $values[$name] ?? '';
            $inputfield->set("value{$langId}", $value);
        }
    }

    /**
     * Build and return the inputfield for a given SEO config, e.g. meta description.
     *
     * @param string $group
     * @param string $name
     * @param array $options
     *
     * @return \ProcessWire\Inputfield
     */
    private function getInputfield($group, $name, array $options)
    {
        $getter = 'getInputfield' . ucfirst($group);

        $inputfield = $this->{$getter}($name, $options);

        $inputfield->attr('name', sprintf('%s_%s', $group, $name));
        $inputfield->label = $options['label'] ?? $name;
        $inputfield->description = $options['description'] ?? '';
        $inputfield->notes = $options['notes'] ?? '';
        $inputfield->useLanguages = $options['translatable'] ?? false;

        return $inputfield;
    }

    private function getInputfieldTwitter($name, array $options)
    {
        switch ($name) {
            case 'card':
                $inputfield = $this->wire('modules')->get('InputfieldSelect');
                $inputfield->addOptions([
                    'summary' => 'summary',
                    'summary_large_image' => 'summary_large_image',
                    'app' => 'app',
                    'player' => 'player',
                ]);
                break;
            default:
                $inputfield = $this->wire('modules')->get('InputfieldText');
        }

        return $inputfield;
    }

    private function getInputfieldSitemap($name, array $options)
    {
        switch ($name) {
            case 'include':
                $inputfield = $this->wire('modules')->get('InputfieldCheckbox');
                $inputfield->uncheckedValue = 0;
                break;
            case 'changeFrequency':
                $inputfield = $this->wire('modules')->get('InputfieldSelect');
                $inputfield->showIf = 'sitemap_include=1';
                $inputfield->addOptions([
                    'hourly' => 'Hourly',
                    'daily' => 'Daily',
                    'weekly' => 'Weekly',
                    'monthly' => 'Monthly',
                    'yearly' => 'Yearly',
                    'never' => 'Never',
                ]);
                break;
            default:
                $inputfield = $this->wire('modules')->get('InputfieldText');
                $inputfield->showIf = 'sitemap_include=1';
        }

        return $inputfield;
    }

    private function getInputfieldOpengraph($name, array $options)
    {
        $inputfield = $this->getInputfieldMeta($name, $options);
        $inputfield->showCount = InputfieldText::showCountNone;

        return $inputfield;
    }

    private function getInputfieldMeta($name, array $options)
    {
        $type = ($name === 'description') ? 'InputfieldTextarea' : 'InputfieldText';

        $inputfield = $this->wire('modules')->get($type);

        if ($name === 'title' || $name === 'description') {
            $inputfield->maxLength = 0;
            $inputfield->showCount = InputfieldText::showCountChars;
        }

        return $inputfield;
    }

    private function getInputfieldRobots($name, array $options)
    {
        $inputfield = $this->wire('modules')->get('InputfieldCheckbox');
        $inputfield->uncheckedValue = 0;

        return $inputfield;
    }

    private function getInputfieldStructuredData($name, array $options)
    {
        switch ($name) {
            case 'breadcrumb':
                $inputfield = $this->wire('modules')->get('InputfieldCheckbox');
                $inputfield->uncheckedValue = 0;
                $inputfield->label2 = $this->_('Render breadcrumb markup');
                break;
            default:
                $inputfield = $this->wire('modules')->get('InputfieldText');
        }

        return $inputfield;
    }
}
