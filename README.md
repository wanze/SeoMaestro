# Seo Maestro

[![Build Status](https://travis-ci.org/wanze/SeoMaestro.svg?branch=master)](https://travis-ci.org/wanze/SeoMaestro)
[![StyleCI](https://github.styleci.io/repos/168985372/shield?branch=master)](https://github.styleci.io/repos/168985372)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![ProcessWire 3](https://img.shields.io/badge/ProcessWire-3.x-orange.svg)](https://github.com/processwire/processwire)

A ProcessWire module helping you to manage SEO related tasks like a boss! 😎✌️

* Automatically generates and maintains a XML sitemap from your pages.
* Includes a Fieldtype and Inputfield to manage sitemap settings and meta data for pages (title, description, canonical url, Opengraph, Twitter, structured data etc.).
* Multi language support for the sitemap and meta data.
* Configure default values for meta data on template level and let pages inherit or overwrite them individually.
* Map existing fields to meta data, reducing the need to duplicate content for content editors.
* Live preview for content editors how the entered meta data appears on Google.

## Requirements

* ProcessWire `3.0` or newer
* PHP `7.0` or newer

## Installation

Install the module from the [modules directory](https://modules.processwire.com/modules/seo-maestro/) or with Composer:

```
composer require wanze/seo-maestro
```

## Configuration

The _Seo Maestro_ module offers the following configuration:

* **`Enable sitemap generation`** Automatically generates and maintains a sitemap file.
* **`Sitemap path`** Path and filename of the sitemap relative from the ProcessWire root directory.
* **`Cache time`** A time in minutes how long the sitemap should be cached.
* **`Base url`** The base url used for all page links in the sitemap and URL metatags, e.g. `https://yourdomain.com`. If empty, the current domain is used.
* **`Default language`** 2-letter language code of the default language, needed to ensure a correct sitemap for
multilanguage sites.

> Change `sitemap.seomaestro.xml` to `sitemap.xml` once you checked that the sitemap file is correct.
The default name reduces the risk to accidentally overwrite an already existing file.

### Configure Meta Data and Sitemap Settings for Pages

The meta data and the sitemap configuration of each page is managed with the included Fieldtype.
Go ahead and create a new field of type *Seo Maestro*, a good name for the field is `seo` 😄.

* Configure default meta data under _Details_. For text based metatags, you may enter strings or placeholders to 
map existing fields. For example, if your template contains a `lead_text` field which should be used for the 
`description` meta tag by default, use the placeholder `{lead_text}`. It is also possible to combine strings and placeholders. 
The following example appends the company name after a page's title: `{title} | acme.com`.
* The opengraph image tag supports placeholders as well: Simply reference an image field. If the field is holding multiple images, the first
one is used. For example, `{images}` would pick the first image from the `images` field. 
* Each page inherits meta tag values and sitemap configuration by default, but may override them individually when editing a page.
* Under the _Input_ tab, configure which meta data is displayed to the content editor when editing pages.

> ℹ️ Edit the field in the context of a template to override any of the default data per template.

## XML Sitemap

If enabled, the module hooks after `ProcessPageView::finished` to generate the XML sitemap after the request has
been handled by ProcessWire.

* The sitemap is only generated if the current user is logged in and the current page is an admin page.
* It only includes pages of templates having a _Seo Maestro_ field, in order to read the sitemap settings.
* It includes hidden pages.
* It excludes pages not viewable for the guest user.

> ⚠ If your installation has lot of pages and the request takes too long to generate the sitemap, or if you run into
memory problems, it is better disable the automatic generation. Use the `\SeoMaestro\SitemapManager` class to create
the sitemap on your own, e.g. via CLI script.

## Meta Data

### Common

Common meta tags that are not managed with the fieldtype, but rendered by default.

| Tag | Description |
| --- | --- |
| `<link rel="alternate">` | Contains the local url of each active page on multi language sites. |
| `<meta name="generator">` | Let anyone know that your site is powered by ProcessWire ❤️ |

### Fieldtype

The following meta data is managed for each page via _Seo Maestro_ field. Meta tags are organized in so called  _groups_.

| Group | Tags | Description |
| --- | --- | --- |
| `meta` |  `title`<br>`description`<br>`keywords`<br>`canonicalUrl` | Holds the famous `title` and `description` tags that should be optimized for search engines. It is also possible to set a custom canonical URL which by default equals the page's url.
| `opengraph` |  `title`<br>`description`<br>`image`<br>`imageAlt`<br>`type`<br>`image`<br>`locale`<br>`siteName` | Opengraph meta data is read by facebook and other social networks. By default, title and description inherit the values from the meta group. If an image is specified, the `og:image:width`, `og:image:height` and `og:image:type` tags are included automatically at render time. |
| `twitter` |  `card`<br>`site`<br>`creator` | Twitter reads the Opengraph meta data as well, except for a few specific tags managed by this group. |
| `robots` |  `noIndex`<br>`noFollow` | Should robots index a page and follow its links? |
| `structuredData` |  `breadcrumb` | Whether to render structured data (JSON-LD) for [breadcrumbs](https://developers.google.com/search/docs/data-types/breadcrumb). |

### Output Meta Tags

Meta tags must be rendered in the `<head>` region of your templates:

```php
// Render all meta tags, including the common ones.
echo $page->seo;
// or...
echo $page->seo->render();

// Render groups individually, e.g. the opengraph meta data.
echo $page->seo->opengraph->render();
```

## API

The module offers an _easy-to-use_ API to retrieve and modify meta data and sitemap configuration for pages:

```php
// Get a single value.
echo $page->seo->meta->description;

$page->of(false);

// Set values as strings or placeholders to reference the value of another field.
$page->seo->opengraph->description = 'A description for opengraph';
$page->seo->meta->title = '{title}';

// Inherit the Twitter card value from the field configuration.
$page->seo->twitter->card = 'inherit';

// Include the page in the sitemap and bump its priority.
$page->seo->sitemap->include = 1;
$page->seo->sitemap->priority = 0.9;

$page->save();
```

Values are always set for the current language. Switch the user's language to set values in a different language:

```php
$current = $user->language;

$user->language = $languages->get('de');

$page->of(false);
$page->seo->opengraph->title = 'Hallo Welt';
$page->save();

$user->language = $current;
```

### Available Selectors

The _Seo Maestro_ fieldtype does not support to query meta data with selectors, e.g. `seo.meta.title%=foo` won't work.
All meta data is stored as JSON, allowing to add new data anytime without the need to change the database schema.
However, the module stores some useful flags whenever a page is saved, and these flags can be used in selectors:

* `sitemap_include` to quickly query if a page is included or excluded in the sitemap.
* `<group>_inherit` is set to `1`, if a page inherits _all_ meta data of a given group.

**Examples**

Find all pages included in the sitemap:
```
$pages->find('seo.sitemap_include=1');
```

---

Find all pages excluded from the sitemap inheriting all meta and opengraph data:
```
$pages->find('seo.sitemap_include=0,seo.meta_inherit=1,seo.opengraph_inherit=1');
```

## Hooks

Several hooks are available for developers to customize the behaviour of the module.

### ___renderMetatags

Add, remove or modify the rendered metatags of a group.

```php
// Remove the description and canonical URL.
$wire->addHookAfter('SeoMaestro::renderMetatags', function (HookEvent $event) {
    $tags = $event->arguments(0);
    $group = $event->arguments(1);

    if ($group === 'meta') {
        unset($tags['description']);
        unset($tags['canonicalUrl']);
        $event->return = $tags;
    }
});
```

### ___renderSeoDataValue

Modify the value of meta data after being rendered.

```php
// Add the brand name after the title. 
$wire->addHookAfter('SeoMaestro::renderSeoDataValue', function (HookEvent $event) {
    $group = $event->arguments(0);
    $name = $event->arguments(1);
    $value = $event->arguments(2);
    
    if ($group === 'meta' && $name === 'title') {
        $event->return = $value . ' | acme.com';
    }
}
```

### ___alterSeoDataForm

Customize the inputfields of the form containing the SEO data, e.g. change collapsed states or descriptions.

### ___sitemapAlwaysExclude

Specify pages that should never appear in the sitemap, regardless of sitemap settings on page level. The `404` page
is excluded by default.

```php
$wire->addHookAfter('SeoMaestro::sitemapAlwaysExclude', function (HookEvent $event) {
    $pageArray = $event->arguments(0);
    $pageArray->add($excludedPage);
});
```

### ___sitemapItems

Add or modify items in the sitemap.

```php
$item = (new SitemapItem())
    ->set('loc', '/en/my-custom-url')
    ->set('priority', 'custom-priority')
    ->set('changefreq', 'changefreq-custom')
    ->addAlternate('de', '/de/my-custom-url-de');

$wire->addHookAfter('SeoMaestro::sitemapItems', function (HookEvent $event) use ($item) {
    $event->return = array_merge($event->return, [$item]);
});
```

## Running Tests

The module includes [PHPUnit](https://phpunit.de/) based tests cases, located in the `./tests` directory.

* Make sure that the dev dependencies are installed by running `composer install` in `site/modules/SeoMaestro`.
* The tests will create pages, fields and templates. Everything should get cleaned up properly, but you should *never ever* run them
on a production environment 😉.
* Some tests expect a multi language setup to exist. To make them pass, use the multi language site profile provided by
ProcessWire. Check the [.travis.yml](.travis.yml) file for an automated setup.  

To run the tests:

```
cd site/modules/SeoMaestro && vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/src --colors
```  


