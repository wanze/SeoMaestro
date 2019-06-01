<?php

namespace SeoMaestro;

use ProcessWire\Pageimage;
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
            $value = wirePopulateStringTags($value, $this->pageFieldValue->getPage());
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

            $metaName = $this->getMetaName($name);

            $tags[$name] = $this->renderTag($metaName, $value);

            // Add additional image meta tags for the type, width and height.
            if ($name === 'image') {
                $fieldName = $this->getFieldNameFromPlaceholder($this->getUnformatted($name));

                if ($fieldName === false) {
                    // External image source.
                    list($width, $height, $typeId) = @getimagesize($value);

                    if ($width !== null) {
                        $tags['imageType'] = $this->renderTag('image:type', sprintf('image/%s', $this->getImageType($typeId)));
                        $tags['imageWidth'] = $this->renderTag('image:width', $width);
                        $tags['imageHeight'] = $this->renderTag('image:height', $height);
                    }
                } else {
                    // Image from a PageImage object.
                    $pageImage = $this->getPageImage($fieldName);
                    if ($pageImage === null) {
                        continue;
                    }

                    $ext = $pageImage->ext === 'jpg' ? 'jpeg' : $pageImage->ext;
                    $tags['imageType'] = $this->renderTag('image:type', sprintf('image/%s', $ext));
                    $tags['imageWidth'] = $this->renderTag('image:width', $pageImage->width);
                    $tags['imageHeight'] = $this->renderTag('image:height', $pageImage->height);
                }
            }
        }

        $tags['url'] = $this->renderTag('url', $this->pageFieldValue->getPage()->httpUrl);

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
        $pageImages = $this->pageFieldValue->getPage()->getUnformatted($fieldName);

        if (!$pageImages instanceof Pageimages) {
            return null;
        }

        if ($pageImages->count()) {
            return $this->resizeImage($pageImages->first());
        }

        // Check if the image falls back to an image from another page.
        $field = $this->wire('fields')->get($fieldName);
        if (!$field->get('defaultValuePage')) {
            return null;
        }

        $defaultPage = $this->wire('pages')->get((int)$field->get('defaultValuePage'));
        if ($defaultPage->id && $defaultPage->id !== $this->pageFieldValue->getPage()->id) {
            $pageImages = $defaultPage->getUnformatted($fieldName);
            if (!$pageImages->count()) {
                return null;
            }

            return $this->resizeImage($pageImages->first());
        }

        return null;
    }

    private function resizeImage(Pageimage $pageImage)
    {
        $field = $this->getFieldInCurrentContext();

        $width = $field->get('opengraph_image_width');
        $height = $field->get('opengraph_image_height');

        if ($width || $height) {
            if ($width && $height) {
                $pageImage = $pageImage->size((int)$width, (int)$height);
            } elseif ($width) {
                $pageImage = $pageImage->width((int)$width);
            } elseif ($height) {
                $pageImage = $pageImage->height((int)$height);
            }
        }

        return $pageImage;
    }

    /**
     * @param string $value
     *
     * @return string|bool
     */
    private function getFieldNameFromPlaceholder($value)
    {
        if (preg_match('/^\{(.*)\}$/', $value, $matches)) {
            return $matches[1];
        }

        return false;
    }
}
