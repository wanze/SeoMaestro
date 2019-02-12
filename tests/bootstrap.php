<?php

require_once(dirname(dirname(dirname(dirname(__DIR__)))) . '/index.php');

// Install SeoMaestro, FieldtypeSeoMaestro and InputfieldSeoMaestro modules.
$wire->wire('modules')->get('SeoMaestro');
