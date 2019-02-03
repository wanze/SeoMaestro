<?php

require_once(dirname(dirname(dirname(dirname(__DIR__)))) . '/index.php');

// Install modules SeoMaestro, FieldtypeSeoMaestro and InputfieldSeoMaestro modules.
$wire->wire('modules')->install('SeoMaestro');
