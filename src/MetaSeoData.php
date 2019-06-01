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
        if ($name === 'canonicalUrl') {
            return $this->renderCanonicalUrlValue($value);
        }

        if ($this->containsPlaceholder($value)) {
            $value = wirePopulateStringTags($value, $this->pageFieldValue->getPage());
        }

        if ($name === 'title') {
            $field = $this->getFieldInCurrentContext();
            if ($field->get('meta_title_format')) {
                $value = str_replace('{meta_title}', $value, $field->get('meta_title_format'));
            }
        }

        return $this->encode($value);
    }

    /**
     * @inheritdoc
     */
    protected function sanitizeValue($name, $value)
    {
        return (string)$value;
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

            if ($name === 'title') {
                $tag = $this->renderTitleTag($value);
            } elseif ($name === 'canonicalUrl') {
                $tag = $this->renderCanonicalUrlTag($value);
            } else {
                $tag = $this->renderTag($name, $value);
            }

            $tags[$name] = $tag;
        }

        return $tags;
    }

    private function renderTitleTag($value)
    {
        return sprintf('<title>%s</title>', $value);
    }

    private function renderTag($name, $value)
    {
        return sprintf('<meta name="%s" content="%s">', $name, $value);
    }

    private function renderCanonicalUrlTag($value)
    {
        return sprintf('<link rel="canonical" href="%s">', $value);
    }

    private function renderCanonicalUrlValue($value)
    {
        $baseUrl = $this->seoMaestro->get('baseUrl');

        if ($value) {
            $canonicalUrl = strpos($value, 'http') === 0 ? $value : $baseUrl . $value;
        } else {
            $page = $this->pageFieldValue->getPage();
            $canonicalUrl = $baseUrl ? $baseUrl . $page->url : $page->httpUrl;
        }

        return $this->encode($canonicalUrl);
    }
}
