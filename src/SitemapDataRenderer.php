<?php

namespace SeoMaestro;

use ProcessWire\Wire;
use function ProcessWire\wirePopulateStringTags;

/**
 * Renderer for data belonging to the sitemap.
 */
class SitemapDataRenderer extends Wire implements SeoDataRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function ___renderValue($name, $value, PageValue $pageValue)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function ___renderMetatags(array $data, PageValue $pageValue)
    {
        return [];
    }
}
