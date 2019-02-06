# Changelog

## [Unreleased]

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
allow installations via Composer.

## [0.1.0] - 2019-02-04

* Initial release of the module 🐣

[Unreleased]: https://github.com/wanze/SeoMaestro/compare/v0.2.1...HEAD
[0.2.1]: https://github.com/wanze/SeoMaestro/releases/tag/v0.2.1
[0.2.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.2.0
[0.1.1]: https://github.com/wanze/SeoMaestro/releases/tag/v0.1.1
[0.1.0]: https://github.com/wanze/SeoMaestro/releases/tag/v0.1.0
