<?php

namespace SeoMaestro;

use ProcessWire\Wire;
use function ProcessWire\wirePopulateStringTags;

/**
 * Renderer for meta tags belonging to the "meta" group.
 */
class MetaDataRenderer extends Wire implements SeoDataRendererInterface
{
    use SeoDataRendererTrait;

    /**
     * {@inheritdoc}
     */
    public function ___renderValue($name, $value, PageValue $pageValue)
    {
        if ($this->containsPlaceholder($value)) {
            return wirePopulateStringTags($value, $pageValue->getPage());
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function ___renderMetatags(array $data, PageValue $pageValue)
    {
        $tags = [];

        foreach ($data as $name => $value) {
            $renderedValue = $this->renderValue($name, $value, $pageValue);
            if (!$renderedValue) {
                continue;
            }

            $tags[] = sprintf('<meta name="%s" value="%s">', $name, $this->encode($renderedValue));
        }

        return $tags;
    }
}
