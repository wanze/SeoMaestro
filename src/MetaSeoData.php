<?php

namespace SeoMaestro;

use function ProcessWire\wirePopulateStringTags;

/**
 * Seo data of the "meta" group.
 */
class MetaSeoData extends SeoDataBase
{
    /**
     * @var string
     */
    protected $group = 'meta';

    /**
     * @inheritdoc
     */
    protected function renderValue($name, $value)
    {
        if ($this->containsPlaceholder($value)) {
            return wirePopulateStringTags($value, $this->pageValue->getPage());
        }

        return $this->encode($value);
    }

    /**
     * @inheritdoc
     */
    protected function sanitizeValue($name, $value)
    {
        return (string) $value;
    }

    /**
     * @inheritdoc
     */
    protected function ___renderMetatags(array $data)
    {
        $tags = [];

        foreach ($data as $name => $unformattedValue) {
            $value = $this->renderValue($name, $unformattedValue);
            if (!$value) {
                continue;
            }

            $tags[] = sprintf('<meta name="%s" value="%s">', $name, $value);
        }

        return $tags;
    }
}
