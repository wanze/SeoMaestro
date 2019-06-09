<?php

namespace SeoMaestro\StructuredData;

use ProcessWire\WireData;

abstract class StructuredData extends WireData implements StructuredDataInterface
{
    public function __toString()
    {
        return $this->render();
    }

    /**
     * {@inheritDoc}
     */
    public function sleepValue()
    {
        return $this->getArray();
    }
}
