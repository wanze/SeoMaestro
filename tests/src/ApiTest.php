<?php

namespace SeoMaestro\Test;

use ProcessWire\WireException;
use SeoMaestro\StructuredData\BreadcrumbStructuredData;

/**
 * Tests for the API provided by SeoMaestro.
 */
class ApiTest extends FunctionalTestCase
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

    /**
     * @test
     */
    public function it_should_save_data_set_with_api()
    {
        $page = $this->createPage($this->template, '/');

        $page->get(self::FIELD_NAME)->meta->title = 'A meta title';
        $page->get(self::FIELD_NAME)->opengraph->description = 'An opengraph description';
        $page->get(self::FIELD_NAME)->twitter->site = '@schtifu';
        $page->get(self::FIELD_NAME)->robots->noIndex = 1;
        $page->get(self::FIELD_NAME)->sitemap->include = 0;
        $page->get(self::FIELD_NAME)->structuredData->breadcrumb = false;

        $page->save();

        // Make sure values are hydrated from database again.
        $this->wire('pages')->uncache($page);
        $page = $this->wire('pages')->get($page->id);

        $this->assertEquals('A meta title', $page->get(self::FIELD_NAME)->meta->title);
        $this->assertEquals('An opengraph description', $page->get(self::FIELD_NAME)->opengraph->description);
        $this->assertEquals('@schtifu', $page->get(self::FIELD_NAME)->twitter->site);
        $this->assertEquals(1, $page->get(self::FIELD_NAME)->robots->noIndex);
        $this->assertEquals(0, $page->get(self::FIELD_NAME)->sitemap->include);
        $this->assertEquals(null, $page->get(self::FIELD_NAME)->structuredData->breadcrumb);
    }

    /**
     * @test
     * @dataProvider invalidDataDataProvider
     */
    public function it_should_throw_an_exception_when_setting_invalid_data($group, $name)
    {
        $page = $this->createPage($this->template, '/');

        $this->expectException(WireException::class);

        $page->get(self::FIELD_NAME)->get($group)->set($name, '');
    }

    public function invalidDataDataProvider()
    {
        return [
            [
                'meta',
                'tiitle',
            ],
            [
                'opengraph',
                'description123'
            ],
            [
                'twitter',
                'username',
            ],
            [
                'robots',
                'robots',
            ],
            [
                'sitemap',
                'include1',
            ],
            [
                'structuredData',
                'crumbbreads',
            ]
        ];
    }

    public function test_multi_language()
    {
        $page = $this->createPage($this->template, '/');
        $page->title = 'A page title EN';

        $this->wire('user')->language = $this->wire('languages')->getDefault();
        $page->get(self::FIELD_NAME)->meta->title = 'Overridden meta title EN';

        $page->save();

        $this->assertEquals('Overridden meta title EN', $page->get(self::FIELD_NAME)->meta->title);

        // Switch language to german.
        $this->wire('user')->language = $this->wire('languages')->get('de');

        $this->assertEquals('Overridden meta title EN', $page->get(self::FIELD_NAME)->meta->title);

        $page->get(self::FIELD_NAME)->meta->title = 'Overriden meta title DE';
        $page->save();

        $this->assertEquals('Overriden meta title DE', $page->get(self::FIELD_NAME)->meta->title);

        $this->wire('user')->language = $this->wire('languages')->getDefault();
    }

    public function test_placeholders_and_inherited_values()
    {
        $page = $this->createPage($this->template, '/');
        $page->title = 'Seo Maestro';
        $page->save();

        $this->field->set('meta_title', '{title} | acme.com');
        $this->field->save();

        $this->assertEquals('Seo Maestro | acme.com', $page->get(self::FIELD_NAME)->meta->title);

        $page->get(self::FIELD_NAME)->meta->title = 'No longer inherited';
        $page->save();

        $this->assertEquals('No longer inherited', $page->get(self::FIELD_NAME)->meta->title);

        $page->get(self::FIELD_NAME)->meta->title = 'inherit';
        $page->save();

        $this->assertEquals('Seo Maestro | acme.com', $page->get(self::FIELD_NAME)->meta->title);
    }

    public function test_render_multi_language()
    {
        $page = $this->createPage($this->template, '/', 'test-render-multi-language');

        $this->wire('user')->language = $this->wire('languages')->getDefault();
        $page->get(self::FIELD_NAME)->meta->description = 'Meta description EN';

        $this->wire('user')->language = $this->wire('languages')->get('de');
        $page->get(self::FIELD_NAME)->meta->description = 'Meta description DE';

        $this->assertContains('Meta description DE', $page->get(self::FIELD_NAME)->render());
        $this->assertNotContains('Meta description EN', $page->get(self::FIELD_NAME)->render());

        $this->wire('user')->language = $this->wire('languages')->getDefault();

        $this->assertNotContains('Meta description DE', $page->get(self::FIELD_NAME)->render());
        $this->assertContains('Meta description EN', $page->get(self::FIELD_NAME)->render());
    }

    public function test_placeholder_fields_containing_html_are_encoded()
    {
        $page = $this->createPage($this->template, '/');
        $page->title = '<a href="">Seo & Maestro</a>';
        $page->save();

        $this->assertEquals('Seo &amp; Maestro', $page->get(self::FIELD_NAME)->meta->title);
    }

    public function test_render_all_metatags()
    {
        $page = $this->createPage($this->template, '/', 'a-page-en');
        $page->title = 'A Page';

        $de = $this->wire('languages')->get('de');
        // Make it active in DE but not in FI.
        $page->set("status{$de->id}", 1);
        $page->set("name{$de->id}", 'a-page-de');
        $page->save();

        $expected = '<title>A Page</title>
<link rel="canonical" href="http://localhost/en/a-page-en/">
<meta property="og:title" content="A Page">
<meta property="og:type" content="website">
<meta property="og:url" content="http://localhost/en/a-page-en/">
<meta name="twitter:card" content="summary">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
  {
    "@type": "ListItem",
    "position": 1,
    "name": "A Page",
    "item": "http://localhost/en/a-page-en/"
  }
  ]
}
</script>
<meta name="generator" content="ProcessWire">
<link rel="alternate" href="http://localhost/en/a-page-en/" hreflang="en">
<link rel="alternate" href="http://localhost/en/a-page-en/" hreflang="x-default">
<link rel="alternate" href="http://localhost/de/a-page-de/" hreflang="de">';

        $this->assertEquals($expected, $page->get(self::FIELD_NAME)->render());
        $this->assertEquals($expected, $page->get(self::FIELD_NAME)->__toString());
    }

    public function test_template_context()
    {
        $page = $this->createPage($this->template, '/', 'test-template-context');
        $page->set('title', 'Seo Maestro');

        $this->assertEquals('Seo Maestro', $page->get(self::FIELD_NAME)->meta->title);

        // Set the meta title in the context of the template.
        $field = $this->template->fieldgroup->getField($this->field, true);
        $field->set('meta_title', '{title} in the context of a template');
        $this->wire('fields')->saveFieldgroupContext($field, $this->template->fieldgroup);

        $this->assertEquals('Seo Maestro in the context of a template', $page->get(self::FIELD_NAME)->meta->title);
    }
}
