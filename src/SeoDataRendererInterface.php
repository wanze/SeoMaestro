<?php

namespace SeoMaestro;

/**
 * Contract for all metatag renderers.
 */
interface SeoDataRendererInterface
{
    /**
     * Render the value for a given metatag name and unformatted value.
     *
     * The passed value might contain placeholders or other sort of information that
     * must be transformed into desired output.
     *
     * @param string $name
     * @param string $value
     * @param \SeoMaestro\PageValue $pageValue
     *
     * @return string
     */
    public function ___renderValue($name, $value, PageValue $pageValue);

    /**
     * Render the metatags markup from the given data.
     *
     * @param array $data
     * @param \SeoMaestro\PageValue $pageValue
     *
     * @return array
     */
    public function ___renderMetatags(array $data, PageValue $pageValue);
}
