# What SEOEarth stores

Kept up to date with every phase. Used for the privacy section of readme.txt and for uninstall behaviour.

## Options (per site; `wp_options`, or `wp_N_options` on multisite)

| Option | Autoload | Contents | Created | Deleted on uninstall |
|---|---|---|---|---|
| `seoearth_settings` | yes | Site-wide settings (see table below) | First time settings are saved | Only if "Remove all SEOEarth data" is ticked |
| `seoearth_db_version` | yes | Data version string, e.g. `0.1.0` | First request after activation | Only if "Remove all SEOEarth data" is ticked (kept otherwise so a reinstall upgrades correctly) |
| `seoearth_migration_lock` | no | Unix timestamp; exists only while an upgrade runs | During upgrades | Always |

### `seoearth_settings` keys

| Key | Type | Default |
|---|---|---|
| `separator` | one of `hyphen`, `ndash`, `mdash`, `pipe`, `middot`, `bullet`, `raquo` | `ndash` |
| `site_represents` | `organization` or `person` | `organization` |
| `organization_name` | plain text | empty (site title is used) |
| `organization_logo` | http(s) URL | empty |
| `default_social_image` | http(s) URL | empty |
| `twitter_site` | X/Twitter handle without `@` | empty |
| `sitemap_enabled` / `sitemap_images` / `sitemap_users` | boolean | `true` |
| `social_og_enabled` / `social_twitter_enabled` | boolean | `true` |
| `remove_data_on_uninstall` | boolean | `false` |

## Personal data
None. SEOEarth does not store information about visitors or users, sets no cookies, and makes no outbound HTTP requests.

Template settings (`title_*` / `desc_*` keys) and page-type indexing switches (`noindex_*` keys, default off) are also stored in `seoearth_settings`; see [TEMPLATES.md](TEMPLATES.md) and [INDEXING.md](INDEXING.md).

## Post meta / term meta

| Key | Stored on | Contents | Who can change it | Deleted on uninstall |
|---|---|---|---|---|
| `_seoearth_title` | posts (any type), terms | Custom SEO title, single-line text, may contain `%%variables%%` | Posts: users who can `edit_post` that post. Terms: users who can `edit_term` | Only if "Remove all SEOEarth data" is ticked |
| `_seoearth_description` | posts (any type), terms | Custom meta description | same | same |
| `_seoearth_canonical` | posts (any type), terms | Custom canonical URL, absolute http(s) only | same | same |
| `_seoearth_robots` | posts (any type), terms | Comma-separated robots tokens from a fixed allowlist | same | same |
| `_seoearth_social_title` | posts (any type), terms | Social sharing title; may contain `%%variables%%` | same | same |
| `_seoearth_social_description` | posts (any type), terms | Social sharing description | same | same |
| `_seoearth_social_image` | posts (any type), terms | Social sharing image URL, absolute http(s) only | same | same |

Keys start with `_`, so they are hidden from the Custom Fields box. They are exposed in the REST API (`meta` field) for the block editor, subject to the capability checks above; the REST API does not show them for posts the requester cannot read.
