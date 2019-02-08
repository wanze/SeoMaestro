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
            $value = wirePopulateStringTags($value, $this->pageFieldValue->getPage());
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
    protected function renderMetatags(array $data)
    {
        $tags = [];

        foreach ($data as $name) {
            $value = $this->get($name);
            if (!$value) {
                continue;
            }

            $tags[$name] = sprintf('<meta name="twitter:%s" content="%s">', $name, $value);
        }

        return $tags;
    }
}
