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
          ├─ Migrator::maybe_run()             — install/upgrade data if the stored version is older
          ├─ do_action( 'seoearth_container' ) — extensions declare/replace services
          ├─ apply_filters( 'seoearth_modules', default modules, $container )
          ├─ for each Module: if should_load() → register()
          └─ do_action( 'seoearth_loaded', $plugin )
```

## Services (`SEOEarth\Container`)

A deliberately tiny container: services are declared with a factory, built on first `get()`, then shared. No autowiring or reflection. A service may be replaced until it has been built (this is how an extension swaps an implementation, via the `seoearth_container` action); replacing a built service throws.

| Service | Purpose |
|---|---|
| `Context` | What kind of request this is (admin, AJAX, cron, CLI, frontend). REST cannot be detected at `plugins_loaded`; REST modules register on `rest_api_init`. |
| `Migrations\Migrator` | Versioned data upgrades (below). |

## Data versioning and migrations

- `seoearth_db_version` (autoloaded) holds the data version. It is compared with `SEOEARTH_VERSION` on every request — one string comparison.
- **Fresh install:** version recorded, `seoearth_installed` fires, no steps run.
- **Upgrade:** every step in `Migrations\Registry` with `stored < version <= code` runs in `version_compare` order; the version is recorded after each step, so a crash resumes at the next request. Then `seoearth_upgraded` fires.
- **Downgrade** (older code, newer data): nothing runs and the stored version is never lowered. Migrations must stay backward-readable for one minor version so rollback is safe.
- **Concurrency:** `seoearth_migration_lock` (not autoloaded) is taken with `add_option()`, which is atomic on the unique `option_name` key. Locks older than 10 minutes are treated as stale.
- Migrations run on normal requests, not only on activation, because network activation and FTP/deploy upgrades never fire the activation hook for every site.

## Settings

```
Settings\Schema     — list of Field definitions (key, section, type, default, label); filter: seoearth_settings_fields
Settings\Settings   — read API: all() / get(); stored values over defaults; wrong-typed values fall back to default
Settings\Sanitizer  — untrusted input → clean array; invalid value keeps the old value + error message
Admin\SettingsPage  — Settings API registration + screen (admin requests only)
```

- **One option**, `seoearth_settings`, autoloaded. Nothing is written until the owner saves, so reading never creates rows.
- **Field types:** `bool`, `text` (`sanitize_text_field`), `url` (absolute http/https only, then `esc_url_raw`), `enum` (must be a declared choice), `twitter_handle` (`^[A-Za-z0-9_]{1,15}$`, leading `@` removed).
- **Checkboxes:** browsers omit unchecked boxes. The form posts a hidden `_sections` list; a missing boolean means "off" only for sections on that form. Programmatic `update_option()` calls change only the keys they pass.
- **Security:** saving goes through core `options.php` → nonce from `settings_fields()` + `option_page_capability_seoearth_settings_group` = `manage_options`. The render callback re-checks the capability. Every value is escaped at output.
- **Idempotent sanitizing:** WordPress runs the sanitize callback twice when the option is first created; sanitizing clean output returns it unchanged (tested).
- Full list of stored data: [docs/DATA.md](docs/DATA.md).

## Titles and descriptions

```
HeadModule (frontend)          pre_get_document_title / wp_title / wp_robots / wp_head (removes core rel_canonical)
  └─ PageContext::from_query   plain description of the page (type, object, page n of m, search, date label)
      └─ Resolver              custom meta → template setting (key e.g. title_pt_post, desc_tax_category)
          ├─ VariableValues    values per context; costly ones are closures (lazy)
          └─ TemplateEngine    one-pass %%var%% replacement, separator cleanup, plain-text result
Robots                         noindex/nofollow/... per page: search+404 → type setting → per-object tokens; adds to core wp_robots
Canonical                      canonical URL from permalink functions, self-referencing pagination, custom override
MetaModule (every request)     register_post_meta / register_term_meta (REST, auth callbacks); adds template settings
TermFields (admin)             SEO title/description rows on term edit screens
Helpers\Text::sanitize_line    sanitize_text_field() that keeps %%variables%% intact
```

- Resolution happens once per request, after the main query. Measured cost on a single post: 0 extra database queries (see Phase 4 report).
- The settings schema cache is keyed on how many times post types/taxonomies have been (un)registered, so late registrations get their template fields.
- User guides: [docs/TEMPLATES.md](docs/TEMPLATES.md), [docs/INDEXING.md](docs/INDEXING.md).
- Robots only ever adds restrictions; core's own noindex (blog not public) is never removed. No canonical is printed on noindex pages.

## XML sitemap

```
SitemapModule      filters on core wp_sitemaps: enabled, add_provider (users), post_types, posts_query_args,
                   posts_entry (lastmod, images), taxonomies, taxonomies_query_args; wp_sitemaps_init swaps renderer
Exclusions         IDs of noindex / explicitly-index / canonicalised-elsewhere posts and terms (IDs only, per-request cache)
Images             featured + same-host content images; batch-primes thumbnails via the_posts on flagged sitemap queries
ImageRenderer      WP_Sitemaps_Renderer subclass: same escaping as core + image:image namespace
```

- Exclusions go into core's query args, so core's page counts (`get_max_num_pages`) stay consistent.
- User guide and measurements: [docs/SITEMAP.md](docs/SITEMAP.md).

## Multisite

- Settings are per site (options table of each site). Nothing is stored network-wide yet.
- Network activation initialises only the current site; every other site initialises itself on its first request. This avoids looping over thousands of sites in one request.
- `uninstall.php` iterates all sites with `switch_to_blog()`.

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
| `seoearth_container` (action) | Declare or replace services before modules are built |
| `seoearth_modules` (filter) | Add/replace modules |
| `seoearth_settings_fields` (filter) | Add settings fields |
| `seoearth_settings_sections` (filter) | Add settings sections |
| `seoearth_settings_section_{id}` (action) | Print help text above a settings section |
| `seoearth_template_variables` (filter) | Add or change `%%variable%%` values |
| `seoearth_head_output_enabled` (filter) | Turn off title/description/canonical/robots output |
| `seoearth_canonical` (filter) | Change or remove the canonical URL |
| `seoearth_sitemap_images` (filter) | Change a post's sitemap images |
| `seoearth_sitemap_image_hosts` (filter) | Hosts whose images count as this site's (e.g. a CDN) |
| `seoearth_loaded` (action) | Run after core modules registered |
| `seoearth_installed` (action) | First install on a site |
| `seoearth_upgraded` (action) | Data upgraded; receives from, to, steps run |
| more added per phase | Documented in each module's docblock |

Free never contains locked or teaser features; Pro is a separate plugin that uses these hooks.
