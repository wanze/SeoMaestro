<?php

namespace SeoMaestro\Test;

use ProcessWire\WireException;
use SeoMaestro\StructuredData\BreadcrumbStructuredData;

class MetaTest extends FunctionalTestCase
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

        $this->assertEquals('Seo Maestro', $page->get(self::FIELD_NAME)->meta->title);
        $this->assertEquals('http://localhost/en/seo-maestro/', $page->get(self::FIELD_NAME)->meta->canonicalUrl);
    }

    public function test_custom_canonical_url()
    {
        $page = $this->createPage($this->template, '/');
        $page->title = 'Seo Maestro';
        $page->get(self::FIELD_NAME)->meta->canonicalUrl = 'http://localhost/en/custom/';
        $page->save();

        $expected = "<title>Seo Maestro</title>\n<link rel=\"canonical\" href=\"http://localhost/en/custom/\">";
        $this->assertEquals($expected, $page->get(self::FIELD_NAME)->meta->render());

        // Relative URL's should use the base url from the module config.
        $page->get(self::FIELD_NAME)->meta->canonicalUrl = '/en/custom/';
        $this->wire('modules')->get('SeoMaestro')->set('baseUrl', 'https://mydomain.com');

        $expected = "<title>Seo Maestro</title>\n<link rel=\"canonical\" href=\"https://mydomain.com/en/custom/\">";
        $this->assertEquals($expected, $page->get(self::FIELD_NAME)->meta->render());

        $this->wire('modules')->get('SeoMaestro')->set('baseUrl', '');
    }

    public function test_render()
    {
        $page = $this->createPage($this->template, '/');
        $page->title = 'Seo Maestro';
        $page->get(self::FIELD_NAME)->meta->description = "This <a href='/foo'>string</a> <b>should</b><br> be sanitized and encode'd correctly\n";
        $page->save();

        $expected = "<title>Seo Maestro</title>\n<meta name=\"description\" content=\"This string should be sanitized and encode&#039;d correctly\">\n<link rel=\"canonical\" href=\"http://localhost/en/untitled-page/\">";
        $this->assertEquals($expected, $page->get(self::FIELD_NAME)->meta->render());

        $page->get(self::FIELD_NAME)->meta->keywords = 'Seo Maestro, ProcessWire, Module';

        $expected = "<title>Seo Maestro</title>\n<meta name=\"description\" content=\"This string should be sanitized and encode&#039;d correctly\">\n<meta name=\"keywords\" content=\"Seo Maestro, ProcessWire, Module\">\n<link rel=\"canonical\" href=\"http://localhost/en/untitled-page/\">";

        $this->assertEquals($expected, $page->get(self::FIELD_NAME)->meta->render());
    }

    public function test_render_meta_title_with_custom_format()
    {
        $page = $this->createPage($this->template, '/');
        $page->title = 'Seo Maestro';
        $page->save();

        $this->field->set('meta_title_format', '{meta_title} | acme.com');
        $this->field->save();

        $this->assertEquals('Seo Maestro | acme.com', $page->get(self::FIELD_NAME)->meta->title);
    }
}
