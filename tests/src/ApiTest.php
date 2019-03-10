<?php

namespace SeoMaestro\Test;

use ProcessWire\WireException;

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

    public function test_default_values()
    {
        $page = $this->createPage($this->template, '/');
        $page->set('title', 'Seo Maestro');
        $page->set('name', 'seo-maestro');
        $page->save();

        $this->assertEquals('Seo Maestro', $page->get(self::FIELD_NAME)->meta->title);
        $this->assertEquals('http://localhost/en/seo-maestro/', $page->get(self::FIELD_NAME)->meta->canonicalUrl);
        $this->assertEquals('Seo Maestro', $page->get(self::FIELD_NAME)->opengraph->title);
        $this->assertEquals('Seo Maestro', $page->get(self::FIELD_NAME)->og->title);
        $this->assertEquals('website', $page->get(self::FIELD_NAME)->opengraph->type);
        $this->assertEquals('summary', $page->get(self::FIELD_NAME)->twitter->card);
        $this->assertEquals(0, $page->get(self::FIELD_NAME)->robots->noIndex);
        $this->assertEquals(0, $page->get(self::FIELD_NAME)->robots->noFollow);
        $this->assertEquals(1, $page->get(self::FIELD_NAME)->sitemap->include);
        $this->assertEquals(0.5, $page->get(self::FIELD_NAME)->sitemap->priority);
        $this->assertEquals('monthly', $page->get(self::FIELD_NAME)->sitemap->changeFrequency);
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

        $page->save();

        // Make sure values are hydrated from database again.
        $this->wire('pages')->uncache($page);
        $page = $this->wire('pages')->get($page->id);

        $this->assertEquals('A meta title', $page->get(self::FIELD_NAME)->meta->title);
        $this->assertEquals('An opengraph description', $page->get(self::FIELD_NAME)->opengraph->description);
        $this->assertEquals('@schtifu', $page->get(self::FIELD_NAME)->twitter->site);
        $this->assertEquals(1, $page->get(self::FIELD_NAME)->robots->noIndex);
        $this->assertEquals(0, $page->get(self::FIELD_NAME)->sitemap->include);
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
                'include1'
            ],
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

    /**
     * @test
     */
    public function it_should_output_the_correct_opengraph_image_url()
    {
        $this->addImageFieldToTemplate('imagesTest');

        $page = $this->createPage($this->template, '/');
        $page->get('imagesTest')->add(dirname(__DIR__) . '/fixtures/schynige-platte.jpg');
        $page->get(self::FIELD_NAME)->opengraph->image = '{imagesTest}';
        $page->save();

        $this->assertEquals($page->get('imagesTest')->first()->httpUrl, $page->get(self::FIELD_NAME)->opengraph->image);

        $page->get(self::FIELD_NAME)->opengraph->image = 'https://seomaestro.ch/some-other-image.jpg';
        $page->save();

        $this->assertEquals('https://seomaestro.ch/some-other-image.jpg', $page->get(self::FIELD_NAME)->opengraph->image);
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

    public function test_render_meta()
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

    public function test_render_opengraph()
    {
        $this->addImageFieldToTemplate('imageOg');

        $page = $this->createPage($this->template, '/');
        $page->title = 'Seo Maestro';
        $page->get('imageOg')->add(dirname(__DIR__) . '/fixtures/schynige-platte.jpg');
        $page->get(self::FIELD_NAME)->opengraph->image = '{imageOg}';
        $page->save();

        $expected = sprintf("<meta property=\"og:title\" content=\"Seo Maestro\">\n<meta property=\"og:image\" content=\"http://localhost/site/assets/files/%s/schynige-platte.jpg\">\n<meta property=\"og:image:type\" content=\"image/jpeg\">\n<meta property=\"og:image:width\" content=\"1024\">\n<meta property=\"og:image:height\" content=\"768\">\n<meta property=\"og:type\" content=\"website\">\n<meta property=\"og:url\" content=\"%s\">", $page->id, $page->httpUrl);
        $this->assertEquals($expected, $page->get(self::FIELD_NAME)->opengraph->render());
    }

    public function test_render_twitter()
    {
        $page = $this->createPage($this->template, '/');
        $page->get(self::FIELD_NAME)->twitter->site = '@schtifu';
        $page->get(self::FIELD_NAME)->twitter->creator = '@schtifu';

        $expected = "<meta name=\"twitter:card\" content=\"summary\">\n<meta name=\"twitter:site\" content=\"@schtifu\">\n<meta name=\"twitter:creator\" content=\"@schtifu\">";
        $this->assertEquals($expected, $page->get(self::FIELD_NAME)->twitter->render());
    }

    public function test_render_robots()
    {
        $page = $this->createPage($this->template, '/');
        $page->get(self::FIELD_NAME)->twitter->site = '@schtifu';
        $page->get(self::FIELD_NAME)->twitter->creator = '@schtifu';

        $this->assertEquals('', $page->get(self::FIELD_NAME)->robots->render());

        $page->get(self::FIELD_NAME)->robots->noIndex = 1;
        $this->assertEquals('<meta name="robots" content="noindex">', $page->get(self::FIELD_NAME)->robots->render());

        $page->get(self::FIELD_NAME)->robots->noFollow = 1;
        $this->assertEquals('<meta name="robots" content="noindex, nofollow">', $page->get(self::FIELD_NAME)->robots->render());
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

        $expected = "<title>A Page</title>\n<link rel=\"canonical\" href=\"http://localhost/en/a-page-en/\">\n<meta property=\"og:title\" content=\"A Page\">\n<meta property=\"og:type\" content=\"website\">\n<meta property=\"og:url\" content=\"http://localhost/en/a-page-en/\">\n<meta name=\"twitter:card\" content=\"summary\">\n<meta name=\"generator\" content=\"ProcessWire\">\n<link rel=\"alternate\" href=\"http://localhost/en/a-page-en/\" hreflang=\"en\">\n<link rel=\"alternate\" href=\"http://localhost/en/a-page-en/\" hreflang=\"x-default\">\n<link rel=\"alternate\" href=\"http://localhost/de/a-page-de/\" hreflang=\"de\">";

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

    private function addImageFieldToTemplate($name)
    {
        $images = $this->createField('FieldtypeImage', $name);
        $this->template->fieldgroup->add($images);
        $this->template->save();

        return $images;
    }
}
