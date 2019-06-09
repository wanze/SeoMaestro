<?php

namespace SeoMaestro\Test;

use PHPUnit\Framework\TestCase;
use ProcessWire\Field;
use ProcessWire\Fieldgroup;
use ProcessWire\ProcessWire;
use ProcessWire\Template;

/**
 * Base class for functional tests.
 */
abstract class FunctionalTestCase extends TestCase
{
    /**
     * @var array
     */
    protected $pages = [];

    /**
     * @var array
     */
    protected $templates = [];

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $hookIds = [];

    /**
     * @var \ProcessWire\ProcessWire
     */
    protected $wire;

    public function __construct(string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->wire = ProcessWire::getCurrentInstance();
    }

    protected function wire($name = '', $value = null)
    {
        return $this->wire->wire($name, $value);
    }

    /**
     * @param $template
     * @param $parent
     * @param string $name
     * @param array $data
     *
     * @return \ProcessWire\Page
     */
    protected function createPage($template, $parent, $name = '', $data = [])
    {
        $page = $this->wire->wire('pages')->add($template, $parent, $name, $data);
        $this->pages[] = $page;

        return $page;
    }

    /**
     * @param string $name
     * @param string $filename
     *
     * @return \ProcessWire\Template
     */
    protected function createTemplate($name, $filename = '')
    {
        $fieldgroup = (new Fieldgroup())
            ->set('name', $name)
            ->save();

        $template = (new Template())
            ->set('name', $name)
            ->set('filename', $filename)
            ->set('fieldgroup', $fieldgroup);
        $template->save();

        $this->templates[] = $template;

        return $template;
    }

    /**
     * @param $type
     * @param string $name
     *
     * @return \ProcessWire\Field
     */
    protected function createField($type, $name)
    {
        $field = (new Field())
            ->setFieldtype($type)
            ->setName($name);
        $field->save();

        $this->fields[] = $field;

        return $field;
    }

    protected function addHookAfter($method, $toObject, $toMethod = null, $options = [])
    {
        $hookId = $this->wire->addHookAfter($method, $toObject, $toMethod, $options);
        $this->hookIds[] = $hookId;

        return $hookId;
    }

    protected function addHookBefore($method, $toObject, $toMethod = null, $options = [])
    {
        $hookId = $this->wire->addHookBefore($method, $toObject, $toMethod, $options);
        $this->hookIds[] = $hookId;

        return $hookId;
    }

    protected function tearDown()
    {
        foreach ($this->pages as $page) {
            $this->wire->wire('pages')->delete($page, true);
        }

        foreach ($this->templates as $template) {
            $this->wire->wire('templates')->delete($template);
            $this->wire->wire('fieldgroups')->delete($template->fieldgroup);
        }

        foreach ($this->fields as $field) {
            $this->wire->wire('fields')->delete($field);
        }

        foreach ($this->hookIds as $hookId) {
            $this->wire->removeHook($hookId);
        }
    }
}
