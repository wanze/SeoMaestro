<?php

namespace SeoMaestro\Test;

use ProcessWire\WireException;
use SeoMaestro\StructuredData\BreadcrumbStructuredData;

class SitemapTest extends FunctionalTestCase
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

        $this->assertEquals(1, $page->get(self::FIELD_NAME)->sitemap->include);
        $this->assertEquals(0.5, $page->get(self::FIELD_NAME)->sitemap->priority);
        $this->assertEquals('monthly', $page->get(self::FIELD_NAME)->sitemap->changeFrequency);
    }
}
