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

## Editor UI

```
Admin\PostTypes       post types that get SEO controls (public + show_ui, not attachment; seoearth_editor_post_types)
Admin\EditorModule    enqueue_block_editor_assets → build/editor (sidebar); adds "custom-fields" support at init:99
Admin\Metabox         Classic Editor box (only when the block editor is not used for that post); nonce + edit_post
Admin\SeoForm         shared field names + form → meta rules (used by Metabox and TermFields)
assets-src/editor/    sidebar: hooks.js (meta via core/editor, debounced analysis), components.js, sidebar.js, utils.js (Jest-tested)
assets/js/metabox.js  Classic Editor script (no build step); renders with textContent only
```

- Both UIs save through the normal paths: the sidebar edits post meta (saved by core over REST, with the meta auth callbacks), the metabox saves on `save_post`. Neither saves anything by itself.
- The search preview shows `preview.title` / `preview.description` from the analysis endpoint, so the editor never re-implements the template engine.
- `babel.config.js` compiles JSX with the classic runtime (`createElement` from `@wordpress/element`); the default automatic runtime would require the `react-jsx-runtime` script, which WordPress only ships from 6.6. `.eslintrc.js` sets the matching pragma.
- Status is never shown by color alone: every result has a symbol and a word (Problem / Improvement / Note / Good).
- User guide: [docs/EDITOR.md](docs/EDITOR.md).

## Social tags

```
CurrentPage        shared per-request resolution (context, title, description, canonical, robots) — used by HeadModule and SocialModule
SocialTags         builds og:* / twitter:* / article:* values (plain text; image: custom → featured → default)
SocialModule       prints at wp_head priority 5 with esc_attr/esc_url; turns off Jetpack OG; settings-screen notice
Compatibility\Conflicts   detects other SEO plugins that print social tags (version constants); SEOEarth then prints none
```

User guide: [docs/SOCIAL.md](docs/SOCIAL.md).

## WooCommerce

```
WooCommerce\WooModule   should_load(): class_exists( 'WooCommerce' ) at plugins_loaded; only filters, never touches WooCommerce data
                         seoearth_robots_directives / seoearth_sitemap_excluded_posts → cart, checkout, account hidden
                         seoearth_og_is_article + seoearth_social_tags → og:type product, product:price:*, product:availability
                         seoearth_schema_article_type + seoearth_schema_webpage_type → no Article, ItemPage
                         seoearth_breadcrumb_trail → Home › Shop › product_cat chain › product
                         woocommerce_structured_data_breadcrumblist / _website → [] while SchemaModule::active()
```

- Tested against real WooCommerce: `npm run test:php:woo` runs the whole integration suite with WooCommerce loaded (wp-env installs the latest stable release); CI runs it on the latest-WordPress leg. PHPStan uses a small declaration file (`tests/phpstan-woocommerce-stubs.php`).
- User guide: [docs/WOOCOMMERCE.md](docs/WOOCOMMERCE.md).

## Redirects

```
Redirects\Paths            pure: source/target normalisation, protected paths, loop detection (unit-tested)
Redirects\Store            CPT seoearth_redirect (every cap = manage_options, not public, not in REST); index option
Redirects\RedirectsModule  parse_request (priority 1, GET/HEAD, not REST/AJAX/cron) → wp_redirect or 410 (404 template)
Redirects\AdminScreen      edit box, list columns, validation in wp_insert_post_data (invalid → draft + notice)
```

- **Fast path:** `seoearth_redirect_index` (source => id/target/type) is rebuilt on every change and autoloaded up to 500 redirects, so matching adds no query; above 500 it loads on demand (1 query, object-cached). It always exists (created on install, self-healing if lost) because a missing option costs a query per request.
- The index is maintained from `RedirectsModule` (loaded in every context), not the admin screen: `save_post`/`trashed_post`/`untrashed_post`/`deleted_post` mark it stale; it is rebuilt once at shutdown, or on the next read. So WP-CLI, imports and code keep it current. The rebuild re-validates sources and targets, because only the admin screen validates on save.
- Matching happens before WordPress runs the main query, so redirected requests skip content queries entirely.
- Title = normalised source path, read raw (`get_post_field`), never through display filters.
- User guide: [docs/REDIRECTS.md](docs/REDIRECTS.md).

## Image SEO

```
Images\ImagesModule   admin only: media list column + "missing alt" filter (pre_get_posts on the main attachment query),
                       report in the "Images" settings section (one COUNT query, only on that screen)
Field::TYPE_IMAGE_URL  stored/validated like TYPE_URL; the settings screen adds a wp.media picker (assets/js/settings.js),
                       shown only to users with upload_files
```

- Read-only by design (requirement: never modify media without explicit action). The picker only fills a form field.
- "Missing" = no `_wp_attachment_image_alt` row, or an empty one. Whitespace-only values are not detected (would need REGEXP).
- User guide: [docs/IMAGES.md](docs/IMAGES.md).

## Breadcrumbs

```
Breadcrumbs\Trail              home → parents → current page (shared with the BreadcrumbList schema piece)
Breadcrumbs\Renderer           trail → <nav aria-label><ol> HTML; every value escaped; settings: home label, separator
Breadcrumbs\BreadcrumbsModule  shortcode, dynamic block seoearth/breadcrumbs (render_callback + block supports), inline layout CSS
src/functions.php              seoearth_breadcrumbs() / seoearth_get_breadcrumbs() for themes (loaded by seoearth.php)
assets-src/blocks/breadcrumbs  editor side of the block: a static sample (the real trail depends on the page being viewed)
```

- Opt-in by placement: nothing prints until the block, shortcode or function is used. The CSS is enqueued only when a trail is printed.
- Measured: pages and archives 0 extra queries; posts at most 1 with a cold object cache (parent category), shared with the schema trail.
- `Helpers\Assets::manifest()` reads build/*/index.asset.php, so an unbuilt checkout (or static analysis in CI) never `require`s a missing file.
- User guide: [docs/BREADCRUMBS.md](docs/BREADCRUMBS.md).

## Structured data (JSON-LD)

```
SchemaModule       prints one <script type="application/ld+json"> at wp_head priority 20; conflict step-aside; settings notice
Graph              builds SchemaContext once, runs every Piece, filters, drops empty values and dangling @id references
SchemaContext      shared plain values (canonical, title, description, publisher, image, trail) and every node's @id
Piece              interface: is_needed( SchemaContext ) / generate( SchemaContext ) → nodes
Pieces/            Publisher (Organization|Person), WebSite, WebPage, PrimaryImage, BreadcrumbList, Article (+ author Person)
Breadcrumbs\Trail  home → parents → current page; shared with the visible breadcrumbs feature
```

- Pieces never call each other; they reference nodes by `@id` from SchemaContext. If a piece is removed, Graph removes references to it.
- JSON is encoded with `JSON_HEX_TAG|AMP|APOS|QUOT`, so no value can close the script element.
- User guide and measurements: [docs/SCHEMA.md](docs/SCHEMA.md).

## SEO analysis

```
AnalysisModule     POST /seoearth/v1/analysis (rest_api_init only; edit_post permission; read-only)
InputFactory       post + unsaved editor values → Input; titles/descriptions rendered by Resolver::resolve_custom
                   exactly as on the frontend; noindex from Robots; one capped query for duplicate keyphrases
Input              plain values + Keyphrase + Document; rules read only this
Document           post HTML → text, word count, first paragraph, subheadings, h1 count, links, image alts (pure PHP)
Keyphrase          case-insensitive whole-word matching; hyphens = spaces; curly quotes = straight; slug matching
Engine             runs Rule objects (seoearth_analysis_rules), sorts worst-first, counts per status — no score
Rules/             one class per check, each returning a Result {status, severity, message, recommendation, metadata}
```

- Rules are pure (no database, no globals), so they are unit-tested without WordPress and can be ported to JavaScript for live feedback in the editor. Until then, the editor phase calls the REST endpoint.
- Analysis runs on the stored content (block markup), not on `the_content` output, to avoid running other plugins' filters on every keystroke. Dynamic blocks and shortcodes are therefore not expanded.
- Measured: ~7,300-word post analysed in about 5 ms, 1 MB peak. Nothing is loaded on frontend requests except one `rest_api_init` hook.
- Two rule sets share the engine: `Engine::seo()` and `Engine::readability()`. The endpoint returns `{ seo, readability }` built from one Input.
- Readability (`src/Readability/`): `Sentences` (sentence splitting, English syllable estimate), `English` (transition words, passive-voice indicator), and one rule class per check. Language-specific rules check `Input::is_english()`; the content language comes from the site locale (`seoearth_content_locale` filter for multilingual sites).
- Measured with both rule sets: ~5,900-word post in about 22 ms, 1 MB peak.
- User guides: [docs/ANALYSIS.md](docs/ANALYSIS.md), [docs/READABILITY.md](docs/READABILITY.md).

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
| `seoearth_social_output_enabled`, `seoearth_social_conflict`, `seoearth_social_tags`, `seoearth_social_image`, `seoearth_og_is_article` (filters) | Social tag control |
| `seoearth_schema_output_enabled`, `seoearth_schema_conflict`, `seoearth_schema_pieces`, `seoearth_schema_graph`, `seoearth_schema_article_type`, `seoearth_schema_search_action` (filters) | Structured data control |
| `seoearth_robots_directives` (filter) | Add or remove robots directives for a page (canonical, schema and sitemap follow) |
| `seoearth_sitemap_excluded_posts` (filter) | Extra post IDs to leave out of a post type's sitemap |
| `seoearth_schema_webpage_type` (filter) | schema.org type of the WebPage node |
| `seoearth_redirect` (filter) | Change or skip the redirect for a request path |
| `seoearth_editor_post_types` (filter) | Post types with the sidebar/metabox |
| `seoearth_analysis_rules`, `seoearth_readability_rules` (filters) | Add, replace or remove analysis rules |
| `seoearth_transition_words`, `seoearth_content_locale` (filters) | Readability word list; content language per post |
| `seoearth_breadcrumb_trail` (filter) | Change the breadcrumb trail (visible breadcrumbs and BreadcrumbList schema) |
| `seoearth_sitemap_image_hosts` (filter) | Hosts whose images count as this site's (e.g. a CDN) |
| `seoearth_loaded` (action) | Run after core modules registered |
| `seoearth_installed` (action) | First install on a site |
| `seoearth_upgraded` (action) | Data upgraded; receives from, to, steps run |
| more added per phase | Documented in each module's docblock |

Free never contains locked or teaser features; Pro is a separate plugin that uses these hooks.
