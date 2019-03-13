# Changelog

## [Unreleased]

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

[Unreleased]: https://github.com/wanze/SeoMaestro/compare/v0.6.0...HEAD
[0.6.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.6.0
[0.5.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.5.0
[0.4.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.4.0
[0.3.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.3.0
[0.2.1]: https://github.com/wanze/SeoMaestro/releases/tag/v0.2.1
[0.2.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.2.0
[0.1.1]: https://github.com/wanze/SeoMaestro/releases/tag/v0.1.1
[0.1.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.1.0
