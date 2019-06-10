<?php

namespace SeoMaestro\Test;

use ProcessWire\WireException;
use SeoMaestro\StructuredData\BreadcrumbStructuredData;

class StructuredDataTest extends FunctionalTestCase
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

        $this->assertInstanceOf(BreadcrumbStructuredData::class, $page->get(self::FIELD_NAME)->structuredData->breadcrumb);
    }

    public function test_render_breadcrumb()
    {
        $parent = $this->createPage($this->template, '/', 'parent');
        $parent->title = 'Parent';
        $parent->save();

        $child = $this->createPage($this->template, $parent, 'child');
        $child->title = 'Child';
        $child->save();

        $grandChild = $this->createPage($this->template, $child, 'grand-child');
        $grandChild->title = 'Grand Child';
        $grandChild->save();

        $expected = '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
  {
    "@type": "ListItem",
    "position": 1,
    "name": "Parent",
    "item": "http://localhost/en/parent/"
  },
  {
    "@type": "ListItem",
    "position": 2,
    "name": "Child",
    "item": "http://localhost/en/parent/child/"
  },
  {
    "@type": "ListItem",
    "position": 3,
    "name": "Grand Child",
    "item": "http://localhost/en/parent/child/grand-child/"
  }
  ]
}
</script>';

        $this->assertInstanceOf(BreadcrumbStructuredData::class, $grandChild->get(self::FIELD_NAME)->structuredData->breadcrumb);
        $this->assertEquals($expected, $grandChild->get(self::FIELD_NAME)->structuredData->breadcrumb->render());
    }

    /**
     * @test
     */
    public function it_should_not_render_breadcrumbs_if_disabled()
    {
        $page = $this->createPage($this->template, '/', 'page');
        $page->title = 'Page';
        $page->get(self::FIELD_NAME)->structuredData->breadcrumb = false;
        $page->save();

        $this->assertEquals(null, $page->get(self::FIELD_NAME)->structuredData->breadcrumb);
        $this->assertEquals('', $page->get(self::FIELD_NAME)->structuredData->render());
    }
}
