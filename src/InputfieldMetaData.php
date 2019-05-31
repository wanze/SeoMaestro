<?php

namespace SeoMaestro;

use ProcessWire\Inputfield;
use ProcessWire\InputfieldCheckbox;
use ProcessWire\InputfieldFieldset;

/**
 * An inputfield to manage SEO data.
 */
class InputfieldMetaData extends InputfieldFieldset
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
     * @var \SeoMaestro\PageFieldValue
     */
    private $pageValue;

    /**
     * @param string $name
     * @param \SeoMaestro\PageFieldValue $pageValue
     * @param \ProcessWire\Inputfield $inputfield
     */
    public function __construct($name, PageFieldValue $pageValue, Inputfield $inputfield)
    {
        parent::__construct();

        $this->name = $name;
        $this->inputfield = $inputfield;
        $this->pageValue = $pageValue;

        $this->setup();
    }

    private function setup()
    {
        $this->addClass('seomaestro-inputfield-metadata');
        $this->label = $this->inputfield->label;
        $this->description = $this->inputfield->description;
        $this->notes = $this->inputfield->notes;

        $this->inputfield->attr('name', sprintf('%s_%s', $this->pageValue->getField()->name, $this->name));
        $this->inputfield->addClass('seomaestro-inputfield-embed', 'wrapClass');

        $inherit = $this->wire('modules')->get('InputfieldCheckbox');
        $inherit->label2 = $this->_('Inherit');
        $inherit->attr('name', $this->inputfield->attr('name') . '_inherit');
        $inherit->attr('checked', $this->inputfield->attr('value') === 'inherit');
        $inherit->columnWidth = 10;
        // Hiding the label in the header should resolve https://github.com/wanze/SeoMaestro/issues/9, but it does not work.
        // I think this is a bug, introduced with this commit: https://github.com/processwire/processwire/commit/5b45d17991b9de4755a1ab859bf06958812da0f6#diff-da75cd1e018eb711563f54af265fd9e3
        $inherit->skipLabel = Inputfield::skipLabelHeader;
        $inherit->addClass('seomaestro-inputfield-embed', 'wrapClass');
        $inherit->wrapAttr('data-seomaestro-metadata-inherit', $this->inputfield->name);

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
        $preview->addClass('seomaestro-inputfield-embed');

        if ($this->inputfield->attr('value') === 'inherit') {
            $preview->attr('value', $value);

            if ($this->inputfield instanceof InputfieldCheckbox) {
                $this->inputfield->uncheckedValue = 0;
                $this->inputfield->checkedValue = 1;
                $this->inputfield->attr('checked', '');
            }

            $this->inputfield->attr('value', '');

            foreach ($this->wire('languages') ?: [] as $language) {
                if ($language->isDefault()) {
                    continue;
                }
                $this->inputfield->attr(sprintf('value%s', $language->id), '');
            }
        } else {
            $preview->attr('value', $inheritedValue);
        }

        // Add a little preview image for the opengraph image :)
        if ($this->name === 'opengraph_image' && $preview->attr('value')) {
            $image = sprintf('<img class="seomaestro-opengraph-image" src="%s" alt="%s">',
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
