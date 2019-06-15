<?php

namespace ProcessWire;

use SeoMaestro\FormManager;
use SeoMaestro\PageFieldValue;

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
            'version' => '0.8.0',
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
        if ($value instanceof PageFieldValue) {
            return $value;
        }

        return $this->getBlankValue($page, $field);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlankValue(Page $page, Field $field)
    {
        return $this->wire(new PageFieldValue($page, $field, $this->seoMaestro(), []));
    }

    /**
     * {@inheritdoc}
     */
    public function ___wakeupValue(Page $page, Field $field, $value)
    {
        $data = $value['data'] ? json_decode($value['data'], true) : [];

        return $this->wire(new PageFieldValue($page, $field, $this->seoMaestro(), $data));
    }

    /**
     * {@inheritdoc}
     */
    public function ___sleepValue(Page $page, Field $field, $value)
    {
        return [
            'data' => json_encode($value->getArray()),
            'meta_inherit' => $this->doesGroupInheritData('meta', $value) ? 1 : 0,
            'opengraph_inherit' => $this->doesGroupInheritData('opengraph', $value) ? 1 : 0,
            'twitter_inherit' => $this->doesGroupInheritData('twitter', $value) ? 1 : 0,
            'robots_inherit' => $this->doesGroupInheritData('robots', $value) ? 1 : 0,
            'structuredData_inherit' => $this->doesGroupInheritData('structuredData', $value) ? 1 : 0,
            'sitemap_inherit' => $this->doesGroupInheritData('sitemap', $value) ? 1 : 0,
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

        // Add flags to quickly lookup information with selectors.
        // The "inherit" flags are true, if a page inherits all data of a group from the SeoMaestro field.
        $schema['meta_inherit'] = 'tinyint UNSIGNED NOT NULL';
        $schema['opengraph_inherit'] = 'tinyint UNSIGNED NOT NULL';
        $schema['twitter_inherit'] = 'tinyint UNSIGNED NOT NULL';
        $schema['robots_inherit'] = 'tinyint UNSIGNED NOT NULL';
        $schema['sitemap_inherit'] = 'tinyint UNSIGNED NOT NULL';
        $schema['structuredData_inherit'] = 'tinyint UNSIGNED NOT NULL';

        // Is the page included in the sitemap?
        $schema['sitemap_include'] = 'tinyint UNSIGNED NOT NULL';

        // Remove the index on the data column.
        unset($schema['keys']['data']);

        return $schema;
    }

    /**
     * {@inheritdoc}
     */
    public function ___getCompatibleFieldtypes(Field $field)
    {
        return null;
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
            'meta_canonicalUrl' => [
                'label' => $this->_('Canonical URL'),
                'description' => $this->_('The URL of this page that a search engine thinks is most representative from a set of duplicate pages.'),
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
            'structuredData_breadcrumb' => [
                'label' => $this->_('Breadcrumb'),
                'description' => $this->_('Google Search uses breadcrumb markup to categorize the information from the page in search results.'),
                'notes' => $this->_('See: https://developers.google.com/search/docs/data-types/breadcrumb'),
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
            'structuredData_breadcrumb' => 1,
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
            "Set default values for your meta data and the sitemap behaviour. Each page inherits these values by default, but may override them individually.\n For text based metatags, enter a string or a placeholder in the form of `{title}` to map a page field value.\n\n*Note:* Any of the default values might be overridden per template by editing this field in the context of a template.");
        $info->textformatters = ['TextformatterMarkdownExtra'];
        $wrapper->append($info);

        $values = array_merge($this->getDefaultConfig($field), $field->getArray());

        $formManager = new FormManager($this->seoMaestro());
        $form = $formManager->buildForm($this->getSeoData());

        $this->alterConfigForm($form);

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

    private function alterConfigForm(InputfieldWrapper $form)
    {
        // Remove the canonical URL, there is no need to setup a default value.
        $form->remove('meta_canonicalUrl');

        // Add the possibility to set a thumbnail size for the opengraph image.
        $opengraphImage = $form->get('opengraph_image');
        $opengraphImage->columnWidth = 50;

        $width = $this->wire('modules')->get('InputfieldInteger');
        $width->attr('name', 'opengraph_image_width');
        $width->label = $this->_('Image Width');
        $width->description = $this->_('Optionally specify the width when referencing an image field.');
        $width->columnWidth = 25;
        $form->insertAfter($width, $opengraphImage);

        $height = $this->wire('modules')->get('InputfieldInteger');
        $height->attr('name', 'opengraph_image_height');
        $height->label = $this->_('Image Height');
        $height->description = $this->_('Optionally specify the height when referencing an image field.');
        $height->columnWidth = 25;
        $form->insertAfter($height, $width);

        // Meta title format.
        $metaTitle = $form->get('meta_title');
        $metaTitleFormat = $this->wire('modules')->get('InputfieldText');
        $metaTitleFormat->attr('name', 'meta_title_format');
        $metaTitleFormat->label = $this->_('Title Format');
        $metaTitleFormat->description = $this->_('Optionally append additional information to the rendered title. A common pattern is to include the site name or domain. Use the `{meta_title}` placeholder to substitute the rendered title, for example `{meta_title} | acme.com`.');
        $metaTitleFormat->useLanguages = true;
        $form->insertAfter($metaTitleFormat, $metaTitle);
    }

    /**
     * @return \ProcessWire\SeoMaestro
     */
    private function seoMaestro()
    {
        return $this->wire('modules')->get('SeoMaestro');
    }

    /**
     * Check if all data of the given group is inherited in the given page value.
     *
     * @param string $group
     * @param \SeoMaestro\PageFieldValue $pageValue
     *
     * @return bool
     */
    private function doesGroupInheritData($group, PageFieldValue $pageValue)
    {
        $data = $this->getSeoDataByGroup()[$group];

        foreach (array_keys($data) as $name) {
            if ($pageValue->get(sprintf('%s_%s', $group, $name)) !== 'inherit') {
                return false;
            }
        }

        return true;
    }
}
