<?php

namespace SeoMaestro;

use ProcessWire\Wire;
use function ProcessWire\wirePopulateStringTags;

/**
 * Renderer for robots metatags.
 */
class RobotsDataRenderer extends Wire implements SeoDataRendererInterface
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
        $tags = [];
        $contents = [];

        foreach ($data as $name => $value) {
            if ($value) {
                $contents[] = strtolower($name);
            }
        }

        if (count($contents)) {
            $tags[] = sprintf('<meta name="robots" content="%s">', implode(', ', $contents));
        }

        return $tags;
    }
}
