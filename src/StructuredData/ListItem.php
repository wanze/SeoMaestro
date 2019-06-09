<?php

namespace SeoMaestro\StructuredData;

use ProcessWire\WireData;

class ListItem extends WireData
{
    public function __construct()
    {
        parent::__construct();

        $this->set('name', '');
        $this->set('item', '');
    }
}
