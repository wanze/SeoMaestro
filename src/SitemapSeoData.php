<?php

namespace SeoMaestro;

/**
 * Seo data of the "robots" group.
 */
class SitemapSeoData extends SeoDataBase
{
    /**
     * @var array
     */
    public static $changeFrequencies = [
        'never',
        'hourly',
        'daily',
        'weekly',
        'monthly',
        'yearly',
    ];

    protected $group = 'sitemap';

    /**
     * @inheritdoc
     */
    protected function renderValue($name, $value)
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    protected function sanitizeValue($name, $value)
    {
        if ($name === 'changeFrequency') {
            return in_array($value, self::$changeFrequencies) ? $value : 'monthly';
        } elseif ($name === 'include') {
            return (int) $value;
        }

        return (string) $value;
    }

    /**
     * @inheritdoc
     */
    protected function renderMetatags(array $data)
    {
        return [];
    }
}
