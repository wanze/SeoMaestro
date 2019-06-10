<?php

namespace SeoMaestro\Test;

use ProcessWire\WireException;
use SeoMaestro\StructuredData\BreadcrumbStructuredData;

class OpengraphTest extends FunctionalTestCase
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

        $this->assertEquals('Seo Maestro', $page->get(self::FIELD_NAME)->opengraph->title);
        $this->assertEquals('Seo Maestro', $page->get(self::FIELD_NAME)->og->title);
        $this->assertEquals('website', $page->get(self::FIELD_NAME)->opengraph->type);
    }

    /**
     * @test
     */
    public function it_should_output_the_correct_image_url()
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

    public function test_render()
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

    /**
     * @test
     */
    public function it_should_fallback_to_the_image_of_a_default_page()
    {
        $field = $this->addImageFieldToTemplate('imageOg');

        $defaultPage = $this->createPage($this->template, '/', 'defaultPage');
        $defaultPage->title = 'Default Page holding the fallback image';
        $defaultPage->get('imageOg')->add(dirname(__DIR__) . '/fixtures/schynige-platte.jpg');
        $defaultPage->save();

        $field->set('defaultValuePage', $defaultPage->id);
        $field->save();

        $page = $this->createPage($this->template, '/');
        $page->title = 'Seo Maestro';
        $page->get(self::FIELD_NAME)->opengraph->image = '{imageOg}';
        $page->save();

        $expected = sprintf("<meta property=\"og:title\" content=\"Seo Maestro\">\n<meta property=\"og:image\" content=\"http://localhost/site/assets/files/%s/schynige-platte.jpg\">\n<meta property=\"og:image:type\" content=\"image/jpeg\">\n<meta property=\"og:image:width\" content=\"1024\">\n<meta property=\"og:image:height\" content=\"768\">\n<meta property=\"og:type\" content=\"website\">\n<meta property=\"og:url\" content=\"%s\">", $defaultPage->id, $page->httpUrl);
        $this->assertEquals($expected, $page->get(self::FIELD_NAME)->opengraph->render());
    }

    /**
     * @test
     * @dataProvider opengraphImageSizesDataProvider
     */
    public function it_should_resize_the_image_if_width_and_height_are_specified($width, $height, $expectedVariation)
    {
        $this->addImageFieldToTemplate('imageOg');

        $page = $this->createPage($this->template, '/');
        $page->title = 'Seo Maestro';
        $page->get('imageOg')->add(dirname(__DIR__) . '/fixtures/schynige-platte.jpg');
        $page->get(self::FIELD_NAME)->opengraph->image = '{imageOg}';
        $page->save();

        // Restrict opengraph image size on field setting level.
        $field = $this->wire('fields')->get(self::FIELD_NAME);
        $field->set('opengraph_image_width', $width);
        $field->set('opengraph_image_height', $height);
        $field->save();

        $expected = sprintf("<meta property=\"og:title\" content=\"Seo Maestro\">\n<meta property=\"og:image\" content=\"http://localhost/site/assets/files/%s/schynige-platte.%s.jpg\">\n<meta property=\"og:image:type\" content=\"image/jpeg\">\n<meta property=\"og:image:width\" content=\"800\">\n<meta property=\"og:image:height\" content=\"600\">\n<meta property=\"og:type\" content=\"website\">\n<meta property=\"og:url\" content=\"%s\">", $page->id, $expectedVariation, $page->httpUrl);
        $this->assertEquals($expected, $page->get(self::FIELD_NAME)->opengraph->render());
    }

    public function opengraphImageSizesDataProvider()
    {
        return [
            [800, 600, '800x600'],
            [800, null, '800x0'],
            [null, 600, '0x600'],
        ];
    }

    private function addImageFieldToTemplate($name)
    {
        $images = $this->createField('FieldtypeImage', $name);
        $this->template->fieldgroup->add($images);
        $this->template->save();

        return $images;
    }
}
