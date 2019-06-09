<?php

namespace SeoMaestro;

use SeoMaestro\StructuredData\BreadcrumbStructuredData;

/**
 * Seo data of the "structured data" group.
 */
class StructuredDataSeoData extends SeoDataBase
{
    /**
     * @var string
     */
    protected $group = 'structuredData';

    /**
     * @inheritdoc
     */
    protected function renderValue($name, $value)
    {
        if ($name === 'breadcrumb' && $value) {
            return new BreadcrumbStructuredData($this->pageFieldValue->getPage());
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    protected function sanitizeValue($name, $value)
    {
        if ($name === 'breadcrumb') {
            return (bool) $value;
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    protected function renderMetatags(array $data)
    {
        $tags = [];

        foreach ($data as $name) {
            /** @var \SeoMaestro\StructuredData\StructuredDataInterface $value */
            $value = $this->get($name);
            if (!$value) {
                continue;
            }

            $tags[$name] = $value->render();
        }

        return $tags;
    }
}
