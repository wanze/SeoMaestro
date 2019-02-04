<?php

namespace SeoMaestro;

use ProcessWire\Inputfield;
use ProcessWire\InputfieldCheckbox;
use ProcessWire\InputfieldFieldset;

/**
 * An inputfield to manage SEO data.
 */
class InputfieldSeo extends InputfieldFieldset
{
    /**
     * @var \ProcessWire\Inputfield
     */
    private $inputfield;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \SeoMaestro\PageValue
     */
    private $pageValue;

    /**
     * @param string $name
     * @param \SeoMaestro\PageValue $pageValue
     * @param \ProcessWire\Inputfield $inputfield
     */
    public function __construct($name, PageValue $pageValue, Inputfield $inputfield)
    {
        parent::__construct();

        $this->name = $name;
        $this->inputfield = $inputfield;
        $this->pageValue = $pageValue;

        $this->setup();
    }

    private function setup()
    {
        $this->addClass('seo-maestro-inputfield-seo');
        $this->label = $this->inputfield->label;
        $this->description = $this->inputfield->description;
        $this->notes = $this->inputfield->notes;

        $this->inputfield->attr('name', sprintf('%s_%s', $this->pageValue->getField()->name, $this->name));
        $this->inputfield->addClass('seo-maestro-inputfield-embed', 'wrapClass');

        $inherit = $this->wire('modules')->get('InputfieldCheckbox');
        $inherit->label2 = $this->_('Inherit');
        $inherit->attr('name', $this->inputfield->attr('name') . '_inherit');
        $inherit->attr('checked', $this->inputfield->attr('value') === 'inherit');
        $inherit->columnWidth = 10;
        $inherit->addClass('seo-maestro-inputfield-embed', 'wrapClass');

        $this->inputfield->skipLabel = Inputfield::skipLabelHeader;
        $this->inputfield->description = '';
        $this->inputfield->notes = '';
        $this->inputfield->columnWidth = 45;
        $this->inputfield->showIf = $inherit->attr('name') . '=0';

        list($group, $name) = explode('_', $this->name);

        $value = $this->pageValue->get($group)->get($name);
        $inheritedValue = $this->pageValue->get($group)->getInherited($name);

        if ($this->inputfield instanceof InputfieldCheckbox) {
            $value = $value ? $this->_('Yes') : $this->_('No');
            $inheritedValue = $inheritedValue ? $this->_('Yes') : $this->_('No');
        }

        $preview = $this->wire('modules')->get('InputfieldMarkup');
        $preview->columnWidth = 45;
        $preview->showIf = $inherit->attr('name') . '=1';
        $preview->addClass('seo-maestro-inputfield-embed');

        if ($this->inputfield->attr('value') === 'inherit') {
            $preview->attr('value', $value);

            if ($this->inputfield instanceof InputfieldCheckbox) {
                $this->inputfield->uncheckedValue = 0;
                $this->inputfield->checkedValue = 1;
                $this->inputfield->attr('checked', '');
            }

            foreach ($this->wire('languages') ?: [] as $language) {
                $langId = $language->isDefault() ? '' : $language->id;
                $this->inputfield->attr("value{$langId}", '');
            }
        } else {
            $preview->attr('value', $inheritedValue);
        }

        // Add a little preview image for the opengraph image :)
        if ($this->name === 'opengraph_image' && $preview->attr('value')) {
            $image = sprintf('<img class="seo-maestro-opengraph-image" src="%s" alt="%s">',
                $preview->attr('value'),
                $this->_('Preview of the opengraph image')
            );
            $preview->attr('value', $image . $preview->attr('value'));
        }

        $this->append($inherit);
        $this->append($this->inputfield);
        $this->append($preview);
    }
}
