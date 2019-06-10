<?php

namespace SeoMaestro;

use ProcessWire\Inputfield;
use ProcessWire\InputfieldMarkup;

/**
 * An inputfield displaying a preview how Facebook share.
 */
class InputfieldFacebookSharePreview extends InputfieldMarkup
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

        $field = $this->getFieldInCurrentContext();

        $this->addClass('seomaestro-facebookpreview');
        $this->wrapAttr('data-seomaestro-facebookpreview', $field->name);
        $this->label = $this->_('Facebook Share Preview');
    }

    public function ___render()
    {
        $imageUrl = $this->pageFieldValue->get('og')->image;

        $this->attr('value', sprintf(
            '<div class="preview">
               <div class="image" data-image="%s" style="%s"></div>
               <div class="content">
                 <div class="url">%s</div>
                 <div class="title" data-title="%s">%s</div>
                 <div class="desc" data-desc="%s">%s</div>
               </div>
            </div>',
            $this->pageFieldValue->get('og')->getInherited('image'),
            $imageUrl ? "background-image: url('{$imageUrl}');" : '',
            $this->wire('config')->httpHost,
            $this->pageFieldValue->get('og')->getInherited('title'),
            $this->pageFieldValue->get('og')->title,
            $this->pageFieldValue->get('og')->getInherited('description'),
            $this->pageFieldValue->get('og')->description
        ));

        return parent::___render();
    }

    /**
     * Get the field in the context of the page's template.
     *
     * @return \ProcessWire\Field
     */
    private function getFieldInCurrentContext()
    {
        return $this->pageFieldValue
            ->getPage()
            ->get('template')
            ->get('fieldgroup')
            ->getField($this->pageFieldValue->getField(), true);
    }
}
