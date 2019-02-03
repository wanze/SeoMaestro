<?php

namespace SeoMaestro;

use ProcessWire\Pageimages;
use ProcessWire\Wire;
use function ProcessWire\wirePopulateStringTags;

/**
 * Renderer for the opengraph metatags.
 */
class OpengraphDataRenderer extends Wire implements SeoDataRendererInterface
{
    use SeoDataRendererTrait;

    /**
     * {@inheritdoc}
     */
    public function ___renderValue($name, $value, PageValue $pageValue)
    {
        if ($name === 'image') {
            // If the image is a placeholder, resolve the image url from the PageImage.
            $fieldName = $this->getFieldNameFromPlaceholder($value);
            if ($fieldName === false) {
                return $value;
            }

            $pageImage = $this->getPageImage($pageValue->getPage(), $fieldName);
            if ($pageImage === null) {
                return '';
            }

            return $pageImage->httpUrl();
        }

        if ($this->containsPlaceholder($value)) {
            return wirePopulateStringTags($value, $pageValue->getPage());
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function ___renderMetatags(array $data, PageValue $pageValue)
    {
        $tags = [];

        foreach ($data as $name => $value) {
            $renderedValue = $this->renderValue($name, $value, $pageValue);
            if (!$renderedValue) {
                continue;
            }

            $metaName = $this->getMetaName($name);
            $encodedValue = $this->encode($renderedValue);

            $tags[] = $this->renderTag($metaName, $encodedValue);

            // Add additional image meta tags for the type, width and height.
            if ($name === 'image') {
                $fieldName = $this->getFieldNameFromPlaceholder($value);

                if ($fieldName === false) {
                    // External image source.
                    list($width, $height, $typeId) = @getimagesize($renderedValue);

                    if ($width !== null) {
                        $tags[] = $this->renderTag('image:type', sprintf('image/%s', $this->getImageType($typeId)));
                        $tags[] = $this->renderTag('image:width', $width);
                        $tags[] = $this->renderTag('image:height', $height);
                    }
                } else {
                    // Image from a PageImage object.
                    $pageImage = $this->getPageImage($pageValue->getPage(), $fieldName);
                    if ($pageImage === null) {
                        continue;
                    }

                    $tags[] = $this->renderTag('image:type', sprintf('image/%s', $pageImage->ext));
                    $tags[] = $this->renderTag('image:width', $pageImage->width);
                    $tags[] = $this->renderTag('image:height', $pageImage->height);
                }
            }
        }

        $tags[] = $this->renderTag('url', $pageValue->getPage()->httpUrl);

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
     * @param \ProcessWire\Page $page
     * @param string $fieldName
     *
     * @return \ProcessWire\Pageimage|null
     */
    private function getPageImage($page, $fieldName)
    {
        $pageImages = $page->getUnformatted($fieldName);

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
