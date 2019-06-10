<?php

namespace SeoMaestro\Test;

use ProcessWire\WireException;
use SeoMaestro\StructuredData\BreadcrumbStructuredData;

class TwitterTest extends FunctionalTestCase
{
    const TEMPLATE_NAME = 'seoTestTemplate';
    const FIELD_NAME = 'seoTestField';

    /**
     * @var \ProcessWire\Template
     */
    private $template;

    /**
     * @var \ProcessWire\Field
     */
    private $field;

    protected function setUp()
    {
        parent::setUp();

        $fieldtype = $this->wire('fieldtypes')->get('FieldtypeSeoMaestro');
        $this->field = $this->createField($fieldtype, self::FIELD_NAME);

        $this->template = $this->createTemplate(self::TEMPLATE_NAME, dirname(__DIR__) . '/templates/dummy.php');
        $this->template->fieldgroup->add($this->field);
        $this->template->save();
    }

    public function test_default_values()
    {
        $page = $this->createPage($this->template, '/');
        $page->set('title', 'Seo Maestro');
        $page->set('name', 'seo-maestro');
        $page->save();

        $this->assertEquals('summary', $page->get(self::FIELD_NAME)->twitter->card);
    }

    public function test_render()
    {
        $page = $this->createPage($this->template, '/');
        $page->get(self::FIELD_NAME)->twitter->site = '@schtifu';
        $page->get(self::FIELD_NAME)->twitter->creator = '@schtifu';

        $expected = "<meta name=\"twitter:card\" content=\"summary\">\n<meta name=\"twitter:site\" content=\"@schtifu\">\n<meta name=\"twitter:creator\" content=\"@schtifu\">";
        $this->assertEquals($expected, $page->get(self::FIELD_NAME)->twitter->render());
    }
}
