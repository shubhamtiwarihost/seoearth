# Architecture

## Boot sequence

```
seoearth.php
 ├─ defines SEOEARTH_* constants
 ├─ registers SEOEarth\Autoloader (PSR-4, src/ → SEOEarth\)
 ├─ registers activation/deactivation hooks (SEOEarth\Lifecycle)
 └─ plugins_loaded
     ├─ SEOEarth\Requirements — PHP/WP version check; admin notice and stop if unmet
     └─ SEOEarth\Plugin::instance()->boot()
          ├─ apply_filters( 'seoearth_modules', default modules )
          ├─ for each Module: if should_load() → register()
          └─ do_action( 'seoearth_loaded', $plugin )
```

## Modules

Every feature is a class implementing `SEOEarth\Module`:

```php
interface Module {
    public function should_load(): bool; // e.g. is_admin(), class_exists( 'WooCommerce' )
    public function register(): void;    // add_action / add_filter only — no heavy work
}
```

Rules:
- `register()` only attaches hooks. Work happens inside hook callbacks, as late as possible.
- Admin-only modules return `is_admin()` from `should_load()`; frontend-only modules return `! is_admin()`.
- Modules receive their dependencies through the constructor. No module reaches into another module's internals.
- Third parties (including a future Pro add-on) add or replace modules via the `seoearth_modules` filter.

## Why a custom autoloader instead of Composer's

The distributed plugin must not depend on `vendor/`. Composer is used only for development tools. `src/Autoloader.php` is ~20 lines, maps `SEOEarth\X\Y` → `src/X/Y.php`, and rejects class names containing anything other than `[A-Za-z0-9_\]`.

## Why PSR-4 file names instead of `class-foo.php`

WPCS's `WordPress.Files.FileName` expects `class-foo-bar.php`, which cannot be mapped mechanically from the class name. We exclude that single sniff for `src/` (documented in `phpcs.xml.dist`) and keep every other WPCS rule.

## Planned directory layout

```
src/
├── Admin/            Settings pages, notices, metabox (Classic Editor)
├── Analysis/         SEO rule engine (PHP mirror of JS rules, for REST/tests)
├── Readability/      Readability checks
├── Frontend/Head/    One presenter per <head> concern (title, description, robots, canonical)
├── Meta/             Registered post/term meta, template variable engine
├── Schema/Pieces/    One class per schema.org type
├── Sitemap/          Extensions to core wp_sitemaps
├── Social/           Open Graph, Twitter/X
├── Settings/         Option storage, defaults, sanitizers
├── Integrations/     WooCommerce, conflict detection
├── Redirects/        Basic redirects (CPT storage)
├── Breadcrumbs/
├── Autoloader.php  Lifecycle.php  Module.php  Plugin.php  Requirements.php
assets-src/editor/    Gutenberg sidebar source (built by @wordpress/scripts → build/editor/)
```

## Extension points (for a future Pro add-on)

| Hook | Purpose |
|---|---|
| `seoearth_modules` (filter) | Add/replace modules |
| `seoearth_loaded` (action) | Run after core modules registered |
| more added per phase | Documented in each module's docblock |

Free never contains locked or teaser features; Pro is a separate plugin that uses these hooks.
