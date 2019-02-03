<?php

namespace SeoMaestro\Test;

/**
 * Tests for the API of SeoMaestro.
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

        $this->field = $this->createField('FieldtypeSeoMaestro', self::FIELD_NAME);

        $this->template = $this->createTemplate(self::TEMPLATE_NAME, dirname(__DIR__) . '/templates/api.php');
        $this->template->fieldgroup->add($this->field);
        $this->template->save();
    }

    public function test_default_values()
    {
        $page = $this->createPage($this->template, '/');
        $page->set('title', 'Seo Maestro');
        $page->save();

        $this->assertEquals('Seo Maestro', $page->get(self::FIELD_NAME)->meta->title);
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

    public function test_multi_language()
    {
        $page = $this->createPage($this->template, '/');
        $page->title = 'A page title EN';
        $page->get(self::FIELD_NAME)->meta->title = 'Overridden meta title EN';
        $page->save();

        $this->assertEquals('Overridden meta title EN', $page->get(self::FIELD_NAME)->meta->title);

        // Switch language to german.
        $this->wire('user')->language = $this->wire('languages')->get('de');

        $this->assertEquals('Overridden meta title EN', $page->get(self::FIELD_NAME)->meta->title);

        $page->get(self::FIELD_NAME)->meta->title = 'Overriden meta title DE';
        $page->save();

        $this->assertEquals('Overriden meta title DE', $page->get(self::FIELD_NAME)->meta->title);
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

    public function test_render_meta()
    {
        $page = $this->createPage($this->template, '/');
        $page->title = 'Seo Maestro';
        $page->get(self::FIELD_NAME)->meta->description = 'This <string> should be encoded';
        $page->save();

        $expected = "<meta name=\"title\" value=\"Seo Maestro\">\n<meta name=\"description\" value=\"This &lt;string&gt; should be encoded\">";
        $this->assertEquals($expected, $page->get(self::FIELD_NAME)->meta->render());

        $page->get(self::FIELD_NAME)->meta->keywords = 'Seo Maestro, ProcessWire, Module';

        $expected .= "\n<meta name=\"keywords\" value=\"Seo Maestro, ProcessWire, Module\">";
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

        $expected = sprintf("<meta property=\"og:title\" content=\"Seo Maestro\">\n<meta property=\"og:image\" content=\"http://localhost/site/assets/files/%s/schynige-platte.jpg\">\n<meta property=\"og:image:type\" content=\"image/jpg\">\n<meta property=\"og:image:width\" content=\"1024\">\n<meta property=\"og:image:height\" content=\"768\">\n<meta property=\"og:type\" content=\"website\">\n<meta property=\"og:url\" content=\"%s\">", $page->id, $page->httpUrl);
        $this->assertEquals($expected, $page->get(self::FIELD_NAME)->opengraph->render());
    }

    public function test_render_twitter()
    {
        $page = $this->createPage($this->template, '/');
        $page->get(self::FIELD_NAME)->twitter->site = '@schtifu';
        $page->get(self::FIELD_NAME)->twitter->creator = '@schtifu';

        $expected = "<meta name=\"twitter:card\" value=\"summary\">\n<meta name=\"twitter:site\" value=\"@schtifu\">\n<meta name=\"twitter:creator\" value=\"@schtifu\">";
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

    private function addImageFieldToTemplate($name)
    {
        $images = $this->createField('FieldtypeImage', $name);
        $this->template->fieldgroup->add($images);
        $this->template->save();

        return $images;
    }
}
