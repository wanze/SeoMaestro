<?php

namespace SeoMaestro;

use ProcessWire\Language;
use ProcessWire\WireData;
use ProcessWire\WireException;

/**
 * Holds the SEO data of a group such as meta, opengraph, robots or sitemap.
 */
interface SeoDataInterface
{
    /**
     * Get a formatted value with the given name.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function get($name);

    /**
     * Get a formatted value, inherited from the field's configuration.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function getInherited($name);

    /**
     * Get the unformatted value of the given name.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function getUnformatted($name);

    /**
     * Set a value for a given name.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function set($name, $value);

    /**
     * Render all meta data for this group.
     *
     * @return string
     */
    public function render();
}
