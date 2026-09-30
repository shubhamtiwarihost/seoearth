# Changelog

All notable changes are documented here. Format: [Keep a Changelog](https://keepachangelog.com/); versions follow [SemVer](https://semver.org/).

## [Unreleased]
### Added
- Service container with extension hook `seoearth_container`.
- Request `Context` service.
- Versioned data migrations with resume-on-failure and a concurrency lock; `seoearth_installed` / `seoearth_upgraded` actions.
- Multisite-aware uninstall; multisite integration test run in CI.
- Settings framework: typed field schema (`seoearth_settings_fields` filter), sanitizer, settings screen (SEOEarth menu, administrators only), "Settings" link on the Plugins screen.
- Initial settings: title separator, site represents organization/person, name, logo, default sharing image, X username, opt-in data removal.
- SEO titles and meta descriptions for posts, pages, custom post types, terms, archives, search and 404, with `%%variable%%` templates (see docs/TEMPLATES.md).
- Per-post SEO title/description meta, available to the block editor through the REST API with per-post permission checks.
- SEO title/description fields on category, tag and custom taxonomy edit screens.
- Search appearance settings: one title and description template per page type.
- Canonical URLs on all indexable pages (replaces core's singular-only tag; no duplicates, no tracking parameters, self-referencing pagination) with per-post/per-term override.
- Robots meta through core's `wp_robots`: search and 404 noindex, per-page-type noindex switches (off by default), per-post/per-term index/noindex, nofollow, noarchive, nosnippet, noimageindex.
- Settings screen warns when WordPress is set to discourage search engines.
- XML sitemap improvements on top of WordPress core sitemaps: noindex, canonicalised-elsewhere and password-protected content left out; noindex post types/taxonomies removed (explicit "index" items kept); author sitemap follows author-archive indexing; lastmod on WordPress 6.4; image entries (featured + same-site content images). Settings: sitemap, images, authors.
- Open Graph and X Card tags: title/description/image fallbacks, image dimensions/type/alt for media-library images, article times, size-aware X card type, per-post/per-term overrides (term screen fields), site default image and X username, separate on/off switches. Steps aside when another SEO plugin prints social tags; turns off Jetpack's duplicate Open Graph tags.
- Structured data: one schema.org JSON-LD `@graph` per page — Organization or Person (from Site identity settings), WebSite with site search, WebPage/CollectionPage/ProfilePage, featured ImageObject, BreadcrumbList, BlogPosting/Article with author Person. Extensible piece registry (`seoearth_schema_pieces`); references to removed pieces are dropped. On/off setting; steps aside when another SEO plugin prints structured data. Password-protected posts expose no text or image; noindex, search and 404 pages carry only site-level nodes.

### Changed
- Uninstall deletes settings and the data version only when "Remove all SEOEarth data" is ticked.
- `seoearth_db_version` is now autoloaded (it is read on every request).
- Uninstall (with opt-in) also removes per-post and per-term SEO fields.

### Fixed
- Text settings no longer lose `%xx` sequences: `sanitize_text_field()` treated `%%description%%` and `%%date%%` as URL-encoded bytes.

## [0.1.0] — development scaffold (not released)
### Added
- Plugin bootstrap, PSR-4 autoloader, module registry (`seoearth_modules` filter), PHP/WordPress requirement check.
- Tooling: PHPCS (WPCS 3 + PHPCompatibilityWP), PHPStan level 6, PHPUnit + Brain Monkey, wp-env, @wordpress/scripts, GitHub Actions CI.
