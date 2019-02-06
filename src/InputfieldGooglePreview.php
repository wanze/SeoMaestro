<?php

namespace SeoMaestro;

use ProcessWire\Inputfield;
use ProcessWire\InputfieldMarkup;

/**
 * An inputfield displaying a preview how Google renders meta title and description.
 */
class InputfieldGooglePreview extends InputfieldMarkup
{
    /**
     * @var \SeoMaestro\PageFieldValue
     */
    private $pageFieldValue;

    /**
     * @param \SeoMaestro\PageFieldValue $pageFieldValue
     */
    public function __construct(PageFieldValue $pageFieldValue)
    {
        parent::__construct();

        $this->pageFieldValue = $pageFieldValue;

        $this->addClass('seomaestro-googlepreview');
        $this->wrapAttr('data-seomaestro-googlepreview', $this->pageFieldValue->getField()->name);
        $this->label = 'Google Preview';
        $this->skipLabel = Inputfield::skipLabelHeader;
    }

    public function ___render()
    {
        $this->attr('value', sprintf('<div class="preview"><div class="title" data-title="%s">%s</div><div class="link">%s</div><div class="desc" data-desc="%s">%s</div></div>',
            $this->pageFieldValue->get('meta')->getInherited('title'),
            $this->pageFieldValue->get('meta')->title,
            $this->pageFieldValue->getPage()->httpUrl,
            $this->pageFieldValue->get('meta')->getInherited('description'),
            $this->pageFieldValue->get('meta')->description
        ));

        return parent::___render();
    }
}
