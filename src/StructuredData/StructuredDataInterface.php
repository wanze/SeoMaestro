<?php

namespace SeoMaestro\StructuredData;

interface StructuredDataInterface
{
    /**
     * Render the markup.
     *
     * @return string
     */
    public function render();

    /**
     * Get the value as stored in the database.
     *
     * @return array
     */
    public function sleepValue();
}
