<?php

namespace SeoMaestro;

use function ProcessWire\wirePopulateStringTags;

/**
 * Seo data of the "twitter" group.
 */
class TwitterSeoData extends SeoDataBase
{
    /**
     * @var array
     */
    public static $cards = [
        'summary',
        'summary_large_image',
        'app',
        'player',
    ];

    /**
     * @var string
     */
    protected $group = 'twitter';

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
        if ($name === 'card') {
            return in_array($value, self::$cards) ? $value : 'summary';
        }

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

            $tags[] = sprintf('<meta name="twitter:%s" value="%s">', $name, $value);
        }

        return $tags;
    }
}
