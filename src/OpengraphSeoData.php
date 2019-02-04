<?php

namespace SeoMaestro;

use ProcessWire\Pageimages;
use function ProcessWire\wirePopulateStringTags;

/**
 * Seo data of the "opengraph" group.
 */
class OpengraphSeoData extends SeoDataBase
{
    /**
     * @var string
     */
    protected $group = 'opengraph';

    /**
     * @inheritdoc
     */
    protected function renderValue($name, $value)
    {
        if ($name === 'image') {
            // If the image is a placeholder, resolve the image url from the PageImage.
            $fieldName = $this->getFieldNameFromPlaceholder($value);
            if ($fieldName === false) {
                return $this->encode($value);
            }

            $pageImage = $this->getPageImage($fieldName);
            if ($pageImage === null) {
                return '';
            }

            return $pageImage->httpUrl();
        }

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

            $metaName = $this->getMetaName($name);

            $tags[] = $this->renderTag($metaName, $value);

            // Add additional image meta tags for the type, width and height.
            if ($name === 'image') {
                $fieldName = $this->getFieldNameFromPlaceholder($unformattedValue);

                if ($fieldName === false) {
                    // External image source.
                    list($width, $height, $typeId) = @getimagesize($value);

                    if ($width !== null) {
                        $tags[] = $this->renderTag('image:type', sprintf('image/%s', $this->getImageType($typeId)));
                        $tags[] = $this->renderTag('image:width', $width);
                        $tags[] = $this->renderTag('image:height', $height);
                    }
                } else {
                    // Image from a PageImage object.
                    $pageImage = $this->getPageImage($fieldName);
                    if ($pageImage === null) {
                        continue;
                    }

                    $ext = $pageImage->ext === 'jpg' ? 'jpeg' : $pageImage->ext;
                    $tags[] = $this->renderTag('image:type', sprintf('image/%s', $ext));
                    $tags[] = $this->renderTag('image:width', $pageImage->width);
                    $tags[] = $this->renderTag('image:height', $pageImage->height);
                }
            }
        }

        $tags[] = $this->renderTag('url', $this->pageValue->getPage()->httpUrl);

        return $tags;
    }

    private function renderTag($name, $value)
    {
        return sprintf('<meta property="og:%s" content="%s">', $name, $value);
    }

    private function getMetaName($name)
    {
        $mapping = [
            'imageAlt' => 'image:alt',
            'siteName' => 'site_name',
        ];

        return $mapping[$name] ?? $name;
    }

    private function getImageType($id)
    {
        $types = [
            1 => 'gif',
            2 => 'jpeg',
            3 => 'png',
        ];

        return $types[$id] ?? '';
    }

    /**
     * @param string $fieldName
     *
     * @return \ProcessWire\Pageimage|null
     */
    private function getPageImage($fieldName)
    {
        $pageImages = $this->pageValue->getPage()->getUnformatted($fieldName);

        if (!$pageImages instanceof Pageimages || !$pageImages->count()) {
            return null;
        }

        // We always pick the first image.
        return $pageImages->first();
    }

    /**
     * @param string $value
     *
     * @return string|bool
     */
    private function getFieldNameFromPlaceholder($value)
    {
        if (preg_match('/^\{(.*)\}$/', $value, $matches)){
            return $matches[1];
        }

        return false;
    }
}
