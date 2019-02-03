<?php

namespace SeoMaestro;

trait SeoDataRendererTrait
{
    protected function containsPlaceholder($value)
    {
        return preg_match('/\{.*\}/', $value);
    }

    protected function encode($value)
    {
        return $this->wire('sanitizer')->entities1($value);
    }
}
