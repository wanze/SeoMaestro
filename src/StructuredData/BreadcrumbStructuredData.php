<?php

namespace SeoMaestro\StructuredData;

use ProcessWire\Page;
use ProcessWire\TemplateFile;
use ProcessWire\WireArray;

class BreadcrumbStructuredData extends StructuredData
{
    /**
     * @var \ProcessWire\Page
     */
    private $page;

    /**
     * @var \ProcessWire\WireArray
     */
    private $listItems;

    public function __construct(Page $page)
    {
        parent::__construct();

        $this->page = $page;
        $this->listItems = new WireArray();

        $this->buildListItems();
    }

    /**
     * @return \ProcessWire\Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return \ProcessWire\WireArray
     */
    public function getListItems()
    {
        return $this->listItems;
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $template = new TemplateFile(dirname(dirname(__DIR__)) . '/templates/structured_data_breadcrumb.php');
        $template->set('listItems', $this->getListItems());

        return $template->render();
    }

    private function buildListItems()
    {
        foreach ($this->page->parents('template!=home') as $parent) {
            if (!$parent->viewable()) {
                continue;
            }
            $this->listItems->append($this->buildListItem($parent));
        }

        $this->listItems->append($this->buildListItem($this->page));
    }

    private function buildListItem(Page $page)
    {
        $baseUrl = $this->wire('modules')->get('SeoMaestro')->get('baseUrl');
        $url = $baseUrl ? $baseUrl . $page->url : $page->httpUrl;

        return (new ListItem())
            ->set('name', $page->title)
            ->set('item', $url);
    }
}
