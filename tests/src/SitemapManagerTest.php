<?php

namespace SeoMaestro\Test;

use ProcessWire\HookEvent;
use SeoMaestro\SitemapManager;

/**
 * Tests for the SitemapManager.
 *
 * @coversDefaultClass \SeoMaestro\SitemapManager
 */
class SitemapManagerTest extends FunctionalTestCase
{
    const TEMPLATE_NAME = 'seoTestTemplate';
    const FIELD_NAME = 'seoTestField';

    /**
     * @var SitemapManager
     */
    private $sitemapManager;

    /**
     * @var \ProcessWire\Template
     */
    private $template;

    /**
     * @var string
     */
    private $sitemap;


    protected function setUp()
    {
        parent::setUp();

        $fieldtype = $this->wire('fieldtypes')->get('FieldtypeSeoMaestro');
        $field = $this->createField($fieldtype, self::FIELD_NAME);

        $this->template = $this->createTemplate(self::TEMPLATE_NAME, dirname(__DIR__) . '/templates/sitemap-manager.php');
        $this->template->fieldgroup->add($field);
        $this->template->save();

        $this->sitemap = dirname(__DIR__) . '/temp/sitemap.test.xml';

        $this->sitemapManager = new SitemapManager([
                'defaultLanguage' => 'en',
                'baseUrl' => 'https://seomaestro.processwire.com',
            ]
        );
    }

    /**
     * @test
     * @covers ::generate
     */
    public function sitemap_should_contain_included_pages()
    {
        $page = $this->createPage($this->template, '/', 'my-awesome-page');

        $this->sitemapManager->generate($this->sitemap);

        $this->assertFileExists($this->sitemap);
        $this->assertSitemapContains($page->get('name'), true);
    }

    /**
     * @test
     * @covers ::generate
     */
    public function sitemap_should_not_contain_excluded_pages()
    {
        $page = $this->createPage($this->template, '/');
        $page->get(self::FIELD_NAME)->sitemap->include = 0;
        $page->save();

        $this->sitemapManager->generate($this->sitemap);

        $this->assertSitemapContains($page->get('name'), false);
    }

    /**
     * @test
     * @covers ::generate
     */
    public function sitemap_should_not_contain_pages_not_viewable()
    {
        $page = $this->createPage($this->template, '/');

        $this->sitemapManager->generate($this->sitemap);
        $this->assertSitemapContains($page->get('name'), true);

        $this->wire()->addHookAfter('Page::viewable', function (HookEvent $event) use ($page) {
            $hookedPage = $event->object;
            if ($hookedPage->id === $page->id) {
                $event->return = false;
            }
        });

        $this->sitemapManager->generate($this->sitemap);
        $this->assertSitemapContains($page->get('name'), false);
    }

    /**
     * @test
     * @covers ::generate
     */
    public function sitemap_should_contain_links_relative_to_base_url()
    {
        $page = $this->createPage($this->template, '/');

        $baseUrl = 'https://seomaestro.processwire.com';

        $this->sitemapManager->set('baseUrl', '');
        $this->sitemapManager->generate($this->sitemap);
        $this->assertSitemapContains($baseUrl, false);

        $this->sitemapManager->set('baseUrl', $baseUrl);
        $this->sitemapManager->generate($this->sitemap);
        $this->assertSitemapContains($baseUrl, true);
    }

    /**
     * @test
     * @covers ::generate
     */
    public function sitemap_should_contain_correct_priority_and_change_frequency()
    {
        $page = $this->createPage($this->template, '/');
        $page->get(self::FIELD_NAME)->sitemap->priority = 0.333;
        $page->get(self::FIELD_NAME)->sitemap->changeFrequency = 'yearly';
        $page->save();

        $this->sitemapManager->generate($this->sitemap);

        $this->assertSitemapContains('0.333', true);
        $this->assertSitemapContains('yearly', true);
    }

    /**
     * @test
     * @covers ::generate
     */
    public function sitemap_should_only_contain_active_languages()
    {
        $page = $this->createPage($this->template, '/', 'the-english-page-name');
        $de = $this->wire('languages')->get('de');
        $page->set("name{$de->id}", 'the-german-page-name');
        $page->save();

        $this->sitemapManager->generate($this->sitemap);
        $this->assertSitemapContains('the-english-page-name', true);
        $this->assertSitemapContains('the-german-page-name', false);

        $page->set("status{$de->id}", true);
        $page->save();

        $this->sitemapManager->generate($this->sitemap);
        $this->assertSitemapContains('the-english-page-name', true);
        $this->assertSitemapContains('the-german-page-name', true);
    }

    /**
     * @test
     * @covers ::generate
     */
    public function sitemap_should_not_contain_pages_excluded_with_hook()
    {
        $page1 = $this->createPage($this->template, '/');
        $page2 = $this->createPage($this->template, '/');

        $this->sitemapManager->generate($this->sitemap);
        $this->assertSitemapContains($page1->get('name'), true);
        $this->assertSitemapContains($page2->get('name'), true);

        // Exclude $page2 by hooking after SitemapManager::getExcludedPages
        $this->sitemapManager->addHookAfter('getExcludedPages', function (HookEvent $event) use ($page2) {
            /** @var \ProcessWire\PageArray $pageArray */
            $pageArray = $event->return;
            $pageArray->add($page2);
        });

        $this->sitemapManager->generate($this->sitemap);
        $this->assertSitemapContains($page2->get('name'), false);
    }

    protected function tearDown()
    {
        parent::tearDown();

        if (is_file($this->sitemap)) {
            unlink($this->sitemap);
        }
    }

    /**
     * Assert that the generated contains a string.
     *
     * @param string $string
     * @param bool $expected
     */
    private function assertSitemapContains($string, $expected)
    {
        $this->assertEquals($expected, (bool)strpos(file_get_contents($this->sitemap), $string));
    }
}
