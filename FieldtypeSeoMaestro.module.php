<?php

namespace ProcessWire;

use SeoMaestro\FormManager;
use SeoMaestro\PageValue;

/**
 * A fieldtype storing various metatags and sitemap settings for a page.
 */
class FieldtypeSeoMaestro extends Fieldtype implements Module
{
    public static function getModuleInfo()
    {
        return [
            'title' => 'Seo Maestro',
            'summary' => 'A fieldtype storing various meta tags (meta, opengraph, twitter etc.) and sitemap behaviour for pages.',
            'version' => '0.1.0',
            'author' => 'Stefan Wanzenried (Wanze)',
            'installs' => 'InputfieldSeoMaestro',
            'requires' => [
                'PHP>=7.0.0',
                'ProcessWire>=3.0.0',
                'SeoMaestro',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function sanitizeValue(Page $page, Field $field, $value)
    {
        if ($value instanceof PageValue) {
            return $value;
        }

        return $this->getBlankValue($page, $field);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlankValue(Page $page, Field $field)
    {
        return $this->wire(new PageValue($page, $field, []));
    }

    /**
     * {@inheritdoc}
     */
    public function ___wakeupValue(Page $page, Field $field, $value)
    {
        $data = $value['data'] ? json_decode($value['data'], true) : [];

        return $this->wire(new PageValue($page, $field, $data));
    }

    /**
     * {@inheritdoc}
     */
    public function ___sleepValue(Page $page, Field $field, $value)
    {
        return [
            'data' => json_encode($value->getArray()),
            'sitemap_include' => $value->get('sitemap')->include ? 1 : 0,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getInputfield(Page $page, Field $field)
    {
        $inputfield = $this->wire('modules')->get('InputfieldSeoMaestro');
        $inputfield->setField($field);

        return $inputfield;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabaseSchema(Field $field)
    {
        $schema = parent::getDatabaseSchema($field);

        $schema['data'] = 'text NOT NULL';
        $schema['sitemap_include'] = 'tinyint UNSIGNED NOT NULL';

        // Add an index for the sitemap_include flag.
        $schema['keys']['sitemap_include'] = 'KEY sitemap_include (`sitemap_include`)';
        unset($schema['keys']['data']);

        return $schema;
    }

    /**
     * @return array
     */
    public function getSeoData()
    {
        return [
            'meta_title' => [
                'label' => $this->_('Title'),
                'description' => $this->_('Every page should have a unique Meta Title, ideally less than 60 characters long.'),
                'translatable' => true,
            ],
            'meta_description' => [
                'label' => $this->_('Description'),
                'description' => $this->_('Every page should have a unique Meta Description, ideally less than 320 characters long.'),
                'translatable' => true,
            ],
            'meta_keywords' => [
                'label' => $this->_('Keywords'),
                'description' => $this->_('A comma-separated list of keywords about the page. This meta tag is *no longer* supported by most search engines.'),
                'translatable' => true,
            ],
            'opengraph_title' => [
                'label' => $this->_('Title'),
                'translatable' => true,
            ],
            'opengraph_description' => [
                'label' => $this->_('Description'),
                'translatable' => true,
            ],
            'opengraph_image' => [
                'label' => $this->_('Image'),
                'description' => $this->_('This image is used as a social network preview image. Enter an absolute URL to a image or a placeholder for an image field, e.g. `{image}`. If the placeholder references an image field holding multiple images, the first one is used.'),
            ],
            'opengraph_imageAlt' => [
                'label' => $this->_('Image Alt'),
                'description' => $this->_('A description of what is in the image (not a caption). If the page specifies an `og:image` it should specify `og:image:alt`.'),
                'translatable' => true,
            ],
            'opengraph_type' => [
                'label' => $this->_('Type'),
                'description' => $this->_('The type of your object. Depending on the type you specify, other properties may also be required.'),
            ],
            'opengraph_locale' => [
                'label' => $this->_('Locale'),
                'description' => $this->_('The locale these tags are marked up in. Of the format `language_TERRITORY`.'),
                'translatable' => true,
            ],
            'opengraph_siteName' => [
                'label' => $this->_('Site Name'),
                'description' => $this->_('The name which should be displayed for the overall site.'),
            ],
            'twitter_card' => [
                'label' => $this->_('Card'),
                'description' => $this->_('The card type, which will be one of `summary`, `summary_large_image`, `app`, or `player`.'),
            ],
            'twitter_site' => [
                'label' => $this->_('Site'),
                'description' => $this->_('@username for the website used in the card footer.'),
            ],
            'twitter_creator' => [
                'label' => $this->_('Creator'),
                'description' => $this->_('@username for the content creator/author.'),
            ],
            'robots_noIndex' => [
                'label' => $this->_('Prevent search engines from indexing this page'),
            ],
            'robots_noFollow' => [
                'label' => $this->_('Prevent search engines from following links on this page'),
            ],
            'sitemap_include' => [
                'label' => $this->_('Include in Sitemap'),
            ],
            'sitemap_priority' => [
                'label' => $this->_('Priority'),
                'description' => $this->_('The priority of this URL relative to other URLs on your site. Valid values range from `0.0` to `1.0`.'),
            ],
            'sitemap_changeFrequency' => [
                'label' => $this->_('Change Frequency'),
                'description' => $this->_('A hint to search engines on how frequently the page is likely to change.'),
            ],
        ];
    }

    /**
     * Return the Seo data indexed by group.
     *
     * @return array
     */
    public function getSeoDataByGroup()
    {
        $data = [];
        foreach ($this->getSeoData() as $key => $value) {
            list($group, $name) = explode('_', $key);
            $data[$group][$name] = $value;
        }

        return $data;
    }

    /**
     * Get all groups holding the Seo data.
     *
     * @return array
     */
    public function getSeoGroups()
    {
        return array_keys($this->getSeoDataByGroup());
    }

    /**
     * @param \ProcessWire\Field $field
     *
     * @return array
     */
    public function getDefaultConfig(Field $field)
    {
        return [
            'meta_title' => '{title}',
            'opengraph_title' => sprintf('{%s.meta.title}', $field->name),
            'opengraph_description' => sprintf('{%s.meta.description}', $field->name),
            'opengraph_type' => 'website',
            'twitter_card' => 'summary',
            'sitemap_include' => 1,
            'sitemap_priority' => '0.5',
            'sitemap_changeFrequency' => 'monthly',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function ___getConfigInputfields(Field $field)
    {
        $wrapper = parent::___getConfigInputfields($field);

        $info = $this->wire('modules')->get('InputfieldMarkup');
        $info->label = $this->_('Info');
        $info->attr('value',
            "Set default values for your meta tags and the sitemap behaviour. Each page inherits these values by default, but may override them individually.\n For text based metatags, enter a string or a placeholder in the form of `{title}` to map a page field value.\n\n*Note:* Any of the default values might be overridden per template by editing this field in a template context.");
        $info->textformatters = ['TextformatterMarkdownExtra'];
        $wrapper->append($info);

        $values = array_merge($this->getDefaultConfig($field), $field->getArray());

        $formManager = new FormManager();
        $form = $formManager->buildForm($this->getSeoData());
        $formManager->populateValues($form, $values);

        $wrapper->import($form->children());

        return $wrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function ___getConfigAllowContext(Field $field)
    {
        $configGroups = array_map(function ($group) {
            return sprintf('group_%s', $group);
        }, $this->getSeoGroups());

        return array_merge(
            parent::___getConfigAllowContext($field),
            $configGroups
        );
    }
}
