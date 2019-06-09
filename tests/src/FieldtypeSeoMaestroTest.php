<?php

namespace SeoMaestro\Test;

use ProcessWire\HookEvent;

/**
 * Tests for the SeoMaestro fieldtype.
 *
 * @coversDefaultClass \ProcessWire\FieldtypeSeoMaestro
 */
class FieldtypeSeoMaestroTest extends FunctionalTestCase
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
     * @covers ::sleepValue
     */
    public function it_should_store_correct_default_values_when_dehydrating_object()
    {
        $sleepValues = [];

        $this->wire()->addHookAfter('FieldtypeSeoMaestro::sleepValue', function (HookEvent $event) use (&$sleepValues) {
            $sleepValues = $event->return;
        });

        $this->createPage($this->template, '/');

        unset($sleepValues['data']);

        $expected = [
            'meta_inherit' => 1,
            'opengraph_inherit' => 1,
            'twitter_inherit' => 1,
            'robots_inherit' => 1,
            'structuredData_inherit' => 1,
            'sitemap_inherit' => 1,
            'sitemap_include' => 1,
        ];

        $this->assertEquals($expected, $sleepValues);
    }

    /**
     * @test
     * @covers ::sleepValue
     * @dataProvider sleepValuesDataProvider
     */
    public function it_should_store_correct_values_when_dehydrating_object($group, $name, $value, array $expectedSleepValues)
    {
        $sleepValues = [];

        $this->wire()->addHookAfter('FieldtypeSeoMaestro::sleepValue', function (HookEvent $event) use (&$sleepValues) {
            $sleepValues = $event->return;
        });

        $page = $this->createPage($this->template, '/');
        $page->get(self::FIELD_NAME)->get($group)->set($name, $value);
        $page->save();

        unset($sleepValues['data']);

        $this->assertEquals($expectedSleepValues, $sleepValues);
    }

    public function test_selector_sitemap_include()
    {
        $page1 = $this->createPage($this->template, '/');
        $page2 = $this->createPage($this->template, '/');

        $pagesIncluded = $this->wire('pages')->find(sprintf('%s.sitemap_include=1', self::FIELD_NAME));
        $pagesExcluded = $this->wire('pages')->find(sprintf('%s.sitemap_include=0', self::FIELD_NAME));

        $this->assertEquals(2, $pagesIncluded->count());
        $this->assertTrue($pagesIncluded->has($page1));
        $this->assertTrue($pagesIncluded->has($page2));
        $this->assertEquals(0, $pagesExcluded->count());

        $page2->get(self::FIELD_NAME)->sitemap->include = 0;
        $page2->save();

        $pagesIncluded = $this->wire('pages')->find(sprintf('%s.sitemap_include=1', self::FIELD_NAME));
        $pagesExcluded = $this->wire('pages')->find(sprintf('%s.sitemap_include=0', self::FIELD_NAME));

        $this->assertEquals(1, $pagesIncluded->count());
        $this->assertTrue($pagesIncluded->has($page1));
        $this->assertEquals(1, $pagesExcluded->count());
        $this->assertTrue($pagesExcluded->has($page2));
    }

    public function test_selectors_inherit()
    {
        $page1 = $this->createPage($this->template, '/');
        $page2 = $this->createPage($this->template, '/');

        $pagesInheritMeta = $this->wire('pages')->find(sprintf('%s.meta_inherit=1', self::FIELD_NAME));
        $pagesNotInheritingMeta = $this->wire('pages')->find(sprintf('%s.meta_inherit=0', self::FIELD_NAME));

        $this->assertEquals(2, $pagesInheritMeta->count());
        $this->assertTrue($pagesInheritMeta->has($page1));
        $this->assertTrue($pagesInheritMeta->has($page2));
        $this->assertEquals(0, $pagesNotInheritingMeta->count());

        $page2->get(self::FIELD_NAME)->meta->description = 'An overridden description';
        $page2->save();

        $pagesInheritMeta = $this->wire('pages')->find(sprintf('%s.meta_inherit=1', self::FIELD_NAME));
        $pagesNotInheritingMeta = $this->wire('pages')->find(sprintf('%s.meta_inherit=0', self::FIELD_NAME));

        $this->assertEquals(1, $pagesInheritMeta->count());
        $this->assertTrue($pagesInheritMeta->has($page1));
        $this->assertEquals(1, $pagesNotInheritingMeta->count());
        $this->assertTrue($pagesNotInheritingMeta->has($page2));
    }

    /**
     * @return array
     */
    public function sleepValuesDataProvider()
    {
        return [
            [
                'meta',
                'title',
                'An overridden title',
                [
                    'meta_inherit' => 0,
                    'opengraph_inherit' => 1,
                    'twitter_inherit' => 1,
                    'robots_inherit' => 1,
                    'structuredData_inherit' => 1,
                    'sitemap_inherit' => 1,
                    'sitemap_include' => 1,
                ]
            ],
            [
                'meta',
                'title',
                'inherit',
                [
                    'meta_inherit' => 1,
                    'opengraph_inherit' => 1,
                    'twitter_inherit' => 1,
                    'robots_inherit' => 1,
                    'structuredData_inherit' => 1,
                    'sitemap_inherit' => 1,
                    'sitemap_include' => 1,
                ]
            ],
            [
                'opengraph',
                'description',
                'An overridden description',
                [
                    'meta_inherit' => 1,
                    'opengraph_inherit' => 0,
                    'twitter_inherit' => 1,
                    'robots_inherit' => 1,
                    'structuredData_inherit' => 1,
                    'sitemap_inherit' => 1,
                    'sitemap_include' => 1,
                ]
            ],
            [
                'twitter',
                'creator',
                '@schtifu',
                [
                    'meta_inherit' => 1,
                    'opengraph_inherit' => 1,
                    'twitter_inherit' => 0,
                    'robots_inherit' => 1,
                    'structuredData_inherit' => 1,
                    'sitemap_inherit' => 1,
                    'sitemap_include' => 1,
                ]
            ],
            [
                'robots',
                'noIndex',
                1,
                [
                    'meta_inherit' => 1,
                    'opengraph_inherit' => 1,
                    'twitter_inherit' => 1,
                    'robots_inherit' => 0,
                    'structuredData_inherit' => 1,
                    'sitemap_inherit' => 1,
                    'sitemap_include' => 1,
                ]
            ],
            [
                'structuredData',
                'breadcrumb',
                0,
                [
                    'meta_inherit' => 1,
                    'opengraph_inherit' => 1,
                    'twitter_inherit' => 1,
                    'robots_inherit' => 1,
                    'structuredData_inherit' => 0,
                    'sitemap_inherit' => 1,
                    'sitemap_include' => 1,
                ]
            ],
            [
                'sitemap',
                'priority',
                0.8,
                [
                    'meta_inherit' => 1,
                    'opengraph_inherit' => 1,
                    'twitter_inherit' => 1,
                    'robots_inherit' => 1,
                    'structuredData_inherit' => 1,
                    'sitemap_inherit' => 0,
                    'sitemap_include' => 1,
                ]
            ],
            [
                'sitemap',
                'include',
                false,
                [
                    'meta_inherit' => 1,
                    'opengraph_inherit' => 1,
                    'twitter_inherit' => 1,
                    'robots_inherit' => 1,
                    'structuredData_inherit' => 1,
                    'sitemap_inherit' => 0,
                    'sitemap_include' => 0,
                ]
            ],
        ];
    }
}
