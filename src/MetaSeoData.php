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
            $value = wirePopulateStringTags($value, $this->pageFieldValue->getPage());
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
    protected function renderMetatags(array $data)
    {
        $tags = [];

        foreach ($data as $name) {
            $value = $this->get($name);
            if (!$value) {
                continue;
            }

            $tags[$name] = ($name === 'title') ? $this->renderTitleTag($value) : $this->renderTag($name, $value);
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
}
