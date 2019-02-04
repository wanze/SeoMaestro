<?php

namespace SeoMaestro;

/**
 * Provides common helper functions for the meta tag renderers.
 */
trait SeoDataRendererTrait
{
    protected function containsPlaceholder($value)
    {
        return preg_match('/\{.*\}/', $value);
    }

    /**
     * Encode the value to be used in a meta tag.
     *
     * First strips HTML tags and newlines, then encode any entities.
     *
     * @param string $value
     *
     * @return string
     */
    protected function encode($value)
    {
        $sanitizer = $this->wire('sanitizer');

        return $sanitizer->entities1(
            $sanitizer->text($value, ['maxLength' => 0])
        );
    }
}
