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

### Changed
- Uninstall deletes settings and the data version only when "Remove all SEOEarth data" is ticked.
- `seoearth_db_version` is now autoloaded (it is read on every request).

## [0.1.0] — development scaffold (not released)
### Added
- Plugin bootstrap, PSR-4 autoloader, module registry (`seoearth_modules` filter), PHP/WordPress requirement check.
- Tooling: PHPCS (WPCS 3 + PHPCompatibilityWP), PHPStan level 6, PHPUnit + Brain Monkey, wp-env, @wordpress/scripts, GitHub Actions CI.
