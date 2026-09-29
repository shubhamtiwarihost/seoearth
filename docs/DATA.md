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
| `remove_data_on_uninstall` | boolean | `false` |

## Personal data
None. SEOEarth does not store information about visitors or users, sets no cookies, and makes no outbound HTTP requests.

## Post meta / term meta
None yet (added from Phase 5).
