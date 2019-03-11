<?php

namespace SeoMaestro\Test;

use ProcessWire\HookEvent;
use ProcessWire\Page;
use SeoMaestro\SitemapItem;
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

        $this->template = $this->createTemplate(self::TEMPLATE_NAME, dirname(__DIR__) . '/templates/dummy.php');
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
        $page = $this->createPage($this->template, '/', 'sitemap-should-contain-included-pages');

        $this->sitemapManager->generate($this->sitemap);

        $this->assertFileExists($this->sitemap);
        $this->assertSitemapContains($page->get('name'));
    }

    /**
     * @test
     * @covers ::generate
     */
    public function sitemap_should_not_contain_excluded_pages()
    {
        $this->createPage($this->template, '/');

        $page = $this->createPage($this->template, '/', 'sitemap-should-not-contain-excluded-pages');
        $page->get(self::FIELD_NAME)->sitemap->include = 0;
        $page->save();

        $this->sitemapManager->generate($this->sitemap);

        $this->assertSitemapNotContains($page->get('name'));
    }

    /**
     * @test
     * @covers ::generate
     */
    public function sitemap_should_not_contain_pages_not_viewable()
    {
        $page = $this->createPage($this->template, '/', 'sitemap-should-not-contain-pages-not-viewable');

        $this->sitemapManager->generate($this->sitemap);
        $this->assertSitemapContains($page->get('name'));

        // Make $page::viewable() return false by changing the return value with a hook.
        $this->wire()->addHookAfter('Page::viewable', function (HookEvent $event) use ($page) {
            $hookedPage = $event->object;
            if ($hookedPage->id === $page->id) {
                $event->return = false;
            }
        });

        // We need at least one other page or the sitemap won't get generated.
        $this->createPage($this->template, '/');

        $this->sitemapManager->generate($this->sitemap);
        $this->assertSitemapNotContains($page->get('name'));
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
        $this->assertSitemapNotContains($baseUrl);

        $this->sitemapManager->set('baseUrl', $baseUrl);
        $this->sitemapManager->generate($this->sitemap);
        $this->assertSitemapContains($baseUrl);
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

        $this->assertSitemapContains('0.333');
        $this->assertSitemapContains('yearly');
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
        $this->assertSitemapContains('the-english-page-name');
        $this->assertSitemapNotContains('the-german-page-name');

        $page->set("status{$de->id}", true);
        $page->save();

        $this->sitemapManager->generate($this->sitemap);
        $this->assertSitemapContains('the-english-page-name');
        $this->assertSitemapContains('the-german-page-name');
    }

    /**
     * @test
     * @covers ::generate
     */
    public function sitemap_should_not_contain_pages_excluded_with_hook()
    {
        $page1 = $this->createPage($this->template, '/', 'sitemap-should-not-contain-pages-excluded-with-hook-1');
        $page2 = $this->createPage($this->template, '/', 'sitemap-should-not-contain-pages-excluded-with-hook-2');

        $this->sitemapManager->generate($this->sitemap);
        $this->assertSitemapContains($page1->get('name'));
        $this->assertSitemapContains($page2->get('name'));

        // Exclude $page2
        $hookId = $this->addHookAfter('SeoMaestro::sitemapAlwaysExclude', function (HookEvent $event) use ($page2) {
            /** @var \ProcessWire\PageArray $pageArray */
            $pageArray = $event->arguments(0);
            $pageArray->add($page2);
        });

        $this->sitemapManager->generate($this->sitemap);
        $this->assertSitemapContains($page1->get('name'));
        $this->assertSitemapNotContains($page2->get('name'));
    }

    /**
     * @test
     * @covers ::generate
     */
    public function sitemap_should_contain_items_added_with_hook()
    {
        $item = (new SitemapItem())
            ->set('loc', '/en/my-custom-url')
            ->set('priority', 'custom-priority')
            ->set('changefreq', 'changefreq-custom')
            ->addAlternate('de', '/de/my-custom-url-de');

        $this->addHookAfter('SeoMaestro::sitemapItems', function (HookEvent $event) use ($item) {
            $event->return = array_merge($event->return, [$item]);
        });

        $this->sitemapManager->generate($this->sitemap);

        $this->assertSitemapContains($item->loc);
        $this->assertSitemapContains($item->priority);
        $this->assertSitemapContains($item->changefreq);
        $this->assertSitemapContains('/de/my-custom-url-de');
    }

    protected function tearDown()
    {
        parent::tearDown();

        if (is_file($this->sitemap)) {
            unlink($this->sitemap);
        }
    }

    private function assertSitemapNotContains($string)
    {
        $this->assertNotContains($string, file_get_contents($this->sitemap));
    }

    private function assertSitemapContains($string)
    {
        $this->assertContains($string, file_get_contents($this->sitemap));
    }
}
