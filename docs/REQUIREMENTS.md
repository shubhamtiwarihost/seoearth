# SEOEarth — Requirements (v1.0 Free)

Status: **Finalized in Phase 1** (2026-09-29). Changes require a note in the changelog below.

## Identity
| Item | Value |
|---|---|
| Plugin name | SEOEarth |
| Slug / text domain | `seoearth` |
| PHP namespace | `SEOEarth\` |
| Global prefix (functions, options, meta, hooks) | `seoearth_` / `_seoearth_` / `SEOEARTH_` |
| License | GPL-2.0-or-later |

Name check performed 2026-09-29: WordPress.org API returned `Plugin not found` for slug `seoearth`; directory search for "seoearth" returned 0 results. Web search found small unrelated service businesses using "SEO Earth" / "SeoToolsEarth"; no registered software trademark was found. USPTO/WIPO databases were **not** checked directly — this is not legal advice.

## Platform
| Requirement | Minimum | Notes |
|---|---|---|
| PHP | 7.4 | Supported: 7.4, 8.0, 8.1, 8.2, 8.3. CI unit tests on all five; integration on 8.3 (wp-env) and 7.4 (`bin/test-integration-php.sh`). PHP 8.4+ is not declared supported until tested. |
| WordPress | 6.4 | CI matrix: 6.4 and latest |
| Database | Whatever WordPress supports | No custom tables in Free |
| Multisite | Supported | Per-site settings; network activation supported |

`Tested up to` in readme.txt is set only to versions integration tests actually ran on.

## Functional requirements (Free 1.0)
1. **Meta** — title and meta description for posts, pages, CPTs, terms, author/date/search/404/home; template variables (`%%title%%`, `%%site_name%%`, `%%separator%%`, `%%category%%`, `%%author%%`, `%%date%%`, `%%excerpt%%`, extensible via filter).
2. **Canonical** — replaces core `rel_canonical` (never emits twice); per-post override; correct for pagination.
3. **Robots** — via core `wp_robots` filter; index/noindex, follow/nofollow, noarchive, nosnippet, noimageindex; defaults are index,follow; noindex for search results and 404.
4. **Sitemap** — extends core `wp_sitemaps`: exclusions, noindex removal, per-type toggles, lastmod, images.
5. **Social** — Open Graph + Twitter/X cards; site default image; per-post overrides; suppression when a known conflicting plugin is active.
6. **Schema** — single JSON-LD `@graph`: WebSite, WebPage, Article/BlogPosting, BreadcrumbList, Organization/Person. Extensible piece registry.
7. **SEO analysis** — original rule engine; each rule returns `status` (pass/warning/error/info), `severity`, `message`, `recommendation`, `metadata`. No ranking guarantees.
8. **Readability** — original, documented checks (sentence/paragraph length, heading distribution, passive-voice indicators, transition words, Flesch reading ease). English first.
9. **Admin** — Settings API dashboard; Gutenberg sidebar; Classic Editor metabox.
10. **WooCommerce** — optional layer, loaded only if WooCommerce is active.
11. **Breadcrumbs** — opt-in; block, shortcode, template function.
12. **Image SEO** — missing alt detection, default social image. Never modifies media without explicit action.
13. **Redirects (basic)** — 301/302/307/410, loop detection, stored as CPT; `manage_options` only.
14. **Uninstall** — deletes data only if the owner opted in.

## Non-functional requirements
- **Security:** sanitize input, escape output late, nonces + capability checks for every state change, `$wpdb->prepare` only. Release blocker.
- **Privacy:** zero telemetry, zero outbound HTTP requests in Free.
- **Performance:** frontend adds no uncached DB queries beyond WordPress's own for singular views; admin assets only on SEOEarth screens/editor. Measured, not claimed.
- **Accessibility:** WordPress admin a11y practices; status never conveyed by color alone.
- **i18n:** all strings translatable with text domain `seoearth`.
- **Originality:** no code, UI, text, assets or algorithms copied from other SEO plugins.

## Out of scope for Free 1.0 (future Pro candidates)
Advanced schema types, regex redirects/404 log, multiple focus keywords, internal-link suggestions, AI features, analytics integrations, local SEO, advanced WooCommerce.

## Changelog
- 2026-09-29 — Initial version.
- 2026-10-01 — Official PHP support set to 7.4–8.3 (minimum unchanged at 7.4); CI matrix changed from 7.4/8.1/8.3/8.4 to 7.4/8.0/8.1/8.2/8.3.
