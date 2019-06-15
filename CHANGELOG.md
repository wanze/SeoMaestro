# Changelog

## [Unreleased]

## [0.8.0] - 2019-06-15

### Added

* Add facebook share preview for content editors when editing Opengraph meta data
* Add support to extend the rendered meta title with additional information such as the domain or site name ([#11](https://github.com/wanze/SeoMaestro/issues/11))
* Render structured data (JSON-LD) for [breadcrumbs](https://developers.google.com/search/docs/data-types/breadcrumb) via new group `structuredData` ([#10](https://github.com/wanze/SeoMaestro/issues/10))
* Add new meta group `structuredData` which will handle more types of [structured data](https://developers.google.com/search/docs/data-types/article) in the future 🤓 

## [0.7.0] - 2019-06-01

### Fixed

* Fix label being showed above the "inherit" checkbox ([#9](https://github.com/wanze/SeoMaestro/issues/9))

### Added

* Add the possibility to resize the Opengraph image when referencing a page image by specifying a width and/or height ([#7](https://github.com/wanze/SeoMaestro/issues/7))
* Opengraph image: If the referenced image field is empty and pulls the image from another page (default value), the
module now substitutes the default image ([#8](https://github.com/wanze/SeoMaestro/issues/8))

## [0.6.0] - 2019-03-13

### Fixed

* Fix date formatting for the `lastmod` property in the XML sitemap for single language setups 🤦‍️

### Added

* The canonical URL is now part of the `meta` group and can be customized ([#4](https://github.com/wanze/SeoMaestro/issues/4))
* Add possibility to include custom sitemap items by hooking `SeoMaestro::sitemapItems`

## [0.5.0] - 2019-02-17

### Fixed

* Fix wrong url in the `<link rel="alternate" hreflang="x-default">` meta tag
* Fix date formatting for the `lastmod` property in the XML sitemap

## [0.4.0] - 2019-02-08

### Fixed

* Fix meta tag rendering of data in the `meta` and `twitter` groups 🤦‍️ ([#2](https://github.com/wanze/SeoMaestro/issues/2))
* Fix calls to undefined method `Page::localHttpUrl` if `LanguageSupportPageNames` ist not installed ([#1](https://github.com/wanze/SeoMaestro/issues/1))

### Added

* Render common meta tags not managed by the fieldtype:
  * `<link rel="canonical">`
  * `<link rel="alternate">`
  * `<meta name="generator">`
* Add possibility to modify the form rendering SEO data via `SeoMaestro::alterSeoDataForm`  

## [0.3.0] - 2019-02-07

### Fixed

* Fix module not working correctly on single language installations

## [0.2.1] - 2019-02-06

### Fixed

* Fix Google preview not truncating title and description on initial page load 🤦‍️

## [0.2.0] - 2019-02-06

### Fixed

* Fix rendering of the meta title tag
* Fix encoding of meta data containing placeholders

### Added

* Add a preview how Google renders the current meta title and description to the Inputfield
* Add possibility to modify rendered meta data by hooking `SeoMaestro::renderSeoDataValue`
* Add possibility to modify rendered meta tags by hooking `SeoMaestro::renderMetatags`
* Add possibility to exclude pages from the sitemap by hooking `SeoMaestro::sitemapAlwaysExclude` 

## [0.1.1] - 2019-02-04

### Fixed

* Update Composer package name from `wanze/processwire-seomaestro` to `wanze/seo-maestro` to
allow installations via Composer

## [0.1.0] - 2019-02-04

* Initial release of the module 🐣

[Unreleased]: https://github.com/wanze/SeoMaestro/compare/v0.8.0...HEAD
[0.8.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.8.0
[0.7.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.7.0
[0.6.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.6.0
[0.5.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.5.0
[0.4.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.4.0
[0.3.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.3.0
[0.2.1]: https://github.com/wanze/SeoMaestro/releases/tag/v0.2.1
[0.2.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.2.0
[0.1.1]: https://github.com/wanze/SeoMaestro/releases/tag/v0.1.1
[0.1.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.1.0
