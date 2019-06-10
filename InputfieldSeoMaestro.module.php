<?php

namespace ProcessWire;

use SeoMaestro\FormManager;
use SeoMaestro\InputfieldFacebookSharePreview;
use SeoMaestro\InputfieldGooglePreview;
use SeoMaestro\InputfieldMetaData;

/**
 * Inputfield to manage metatags and sitemap settings for a page.
 */
class InputfieldSeoMaestro extends Inputfield
{
    /**
     * @var \ProcessWire\Field
     */
    private $field;

    /**
     * {@inheritdoc}
     */
    public function ___render()
    {
        $form = $this->buildForm();

        return $form->render();
    }

    /**
     * @param \ProcessWire\Field $field
     */
    public function setField(Field $field)
    {
        $this->field = $field;
    }

    /**
     * {@inheritdoc}
     */
    public function ___processInput(WireInputData $input)
    {
        /** @var \SeoMaestro\PageFieldValue $pageValue */
        $pageValue = $this->attr('value');
        /** @var \ProcessWire\FieldtypeSeoMaestro $fieldtype */
        $fieldtype = $this->field->type;

        foreach ($fieldtype->getSeoData() as $name => $info) {
            $key = sprintf('%s_%s', $this->field->name, $name);
            $isTranslatable = $info['translatable'] ?? false;
            $inherit = $this->wire('input')->post($key . '_inherit');

            if ($isTranslatable) {
                $value = ($inherit) ? 'inherit' : $this->wire('input')->post($key);
                if ($value !== null) {
                    $pageValue->set($name, $value);
                }

                foreach ($this->wire('languages') ?: [] as $language) {
                    if ($language->isDefault()) {
                        continue;
                    }

                    $value = ($inherit) ? 'inherit' : $this->wire('input')->post(sprintf('%s__%s', $key, $language->id));

                    if ($value !== null) {
                        $pageValue->set($name . $language->id, $value);
                    }
                }
            } else {
                $value = ($inherit) ? 'inherit' : $this->wire('input')->post($key);

                // Hacky: Checkboxes do not send the unchecked value here... if the inputfield is a checkbox, null === 0.
                if ($value === null) {
                    $form = $this->formManager()->buildForm($this->getDisplayedSeoData());
                    $inputfield = $form->get($name);
                    $value = ($inputfield instanceof InputfieldCheckbox) ? 0 : null;
                }

                if ($value !== null) {
                    $pageValue->set($name, $value);
                }
            }
        }

        // Tell ProcessWire to save the data if any data has been changed in the PageValue object.
        if ($pageValue->isChanged()) {
            $this->trackChange('value');
            $pageValue->getPage()->trackChange($this->attr('name'));
        }

        return $this;
    }

    /**
     * Build the form containing the inputfields to manage seo data.
     *
     * @return \ProcessWire\InputfieldWrapper
     */
    protected function buildForm()
    {
        /** @var \SeoMaestro\PageFieldValue $pageValue */
        $pageValue = $this->attr('value');

        $formManager = $this->formManager();
        $form = $formManager->buildForm($this->getDisplayedSeoData());
        $formManager->populateValues($form, $pageValue->getArray());

        $wrapper = $this->wire(new InputfieldWrapper());

        foreach ($form->children() as $i => $group) {
            $fieldset = $this->wire('modules')->get('InputfieldFieldset');
            $fieldset->label = $group->label;
            $fieldset->description = $group->description;
            $fieldset->notes = $group->notes;
            $fieldset->collapsed = $group->collapsed;
            $wrapper->append($fieldset);

            foreach ($group->children() as $inputfield) {
                // Insert google preview.
                if ($inputfield->attr('name') === 'meta_title') {
                    $preview = new InputfieldGooglePreview($pageValue);
                    $fieldset->append($preview);
                } elseif ($inputfield->attr('name') === 'opengraph_title') {
                    $preview = new InputfieldFacebookSharePreview($pageValue);
                    $fieldset->append($preview);
                }
                $field = new InputfieldMetaData($inputfield->attr('name'), $pageValue, $inputfield);
                $fieldset->append($field);
            }
        }

        return $wrapper;
    }

    /**
     * @return FormManager
     */
    private function formManager()
    {
        return $this->wire(
            new FormManager($this->wire('modules')->get('SeoMaestro'))
        );
    }

    /**
     * @return array
     */
    private function getDisplayedSeoData()
    {
        $displayed = [];

        foreach ($this->field->type->getSeoData() as $key => $data) {
            list($group, $name) = explode('_', $key);

            $showGroup = $this->field->get(sprintf('inputfield_%s_show', $group));
            $showGroup = $showGroup === null ? true : (bool)$showGroup;

            if (!$showGroup) {
                continue;
            }

            $shownFields = $this->field->get(sprintf('inputfield_%s', $group));

            if (is_array($shownFields) && !in_array($name, $shownFields)) {
                continue;
            }

            $displayed[$key] = $data;
        }

        return $displayed;
    }

    /**
     * {@inheritdoc}
     */
    public function ___getConfigInputfields()
    {
        $wrapper = parent::___getConfigInputfields();

        foreach ($this->getSeoData() as $group => $data) {
            $name = sprintf('inputfield_%s_show', $group);
            $showGroup = $this->wire('modules')->get('InputfieldCheckbox');
            $showGroup->label = $this->_('Show') . ' ' . ucfirst($group);
            $showGroup->attr('name', $name);
            $value = $this->field->get($name);
            $checked = $value === null ? true : $value;
            $showGroup->attr('checked', $checked ? 'checked' : '');
            $showGroup->uncheckedValue = 0;
            $showGroup->columnWidth = 25;

            $name = sprintf('inputfield_%s', $group);
            $fields = $this->wire('modules')->get('InputfieldAsmSelect');
            $fields->label = $this->_('Fields');
            $fields->attr('name', $name);
            $fields->addOptions($data);
            $value = $this->field->get($name);
            $fields->attr('value', $value === null ? array_keys($data) : $value);
            $fields->showIf = $showGroup->attr('name') . '=1';
            $fields->columnWidth = 75;

            $wrapper->append($showGroup);
            $wrapper->append($fields);
        }

        return $wrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function ___getConfigAllowContext($field)
    {
        $allowedConfig = [];
        foreach (array_keys($this->getSeoData()) as $group) {
            $allowedConfig[] = sprintf('inputfield_%s_show', $group);
            $allowedConfig[] = sprintf('inputfield_%s', $group);
        }

        return array_merge(
            parent::___getConfigAllowContext($field),
            $allowedConfig
        );
    }

    /**
     * @return array
     */
    private function getSeoData()
    {
        /** @var \ProcessWire\FieldtypeSeoMaestro $fieldType */
        $fieldType = $this->field->type;

        $seoData = [];
        foreach ($fieldType->getSeoData() as $key => $data) {
            list($group, $name) = explode('_', $key);
            $seoData[$group][$name] = $data['label'];
        }

        return $seoData;
    }
}
