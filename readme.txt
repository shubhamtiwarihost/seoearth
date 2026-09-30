=== SEOEarth ===
Contributors: seoearth
Tags: seo, xml sitemap, schema, open graph, breadcrumbs
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Titles, meta descriptions, sitemaps, social cards, structured data, redirects and content analysis. Private by design: no tracking, no external calls.

== Description ==

SEOEarth helps search engines and social networks understand your site, and helps you write content people can find. It is built on WordPress's own features (the core sitemap, the robots API, the block editor) and stays out of the way on the pages your visitors load.

= Search appearance =

* SEO titles and meta descriptions for posts, pages, custom post types, categories, tags, archives, search and 404 pages.
* Templates per content type with variables such as `%%title%%`, `%%site_name%%`, `%%category%%` and `%%excerpt%%`.
* Canonical URLs on every indexable page, including correct pagination.
* Search engine visibility per page and per content type (noindex, nofollow, noarchive, nosnippet, noimageindex).

= Sitemap =

* Improves the XML sitemap built into WordPress: hidden pages, password-protected posts and pages that point elsewhere are left out, and images and last-modified dates are added.

= Social sharing =

* Open Graph and X (Twitter) Card tags with title, description and image fallbacks, image sizes and alt text, and per-post overrides.

= Structured data =

* One connected schema.org graph per page: Organization or Person, WebSite, WebPage, BlogPosting/Article with author, featured image and BreadcrumbList.

= Content analysis in the editor =

* A sidebar in the block editor (and a box in the Classic Editor) with a search result preview.
* 16 SEO checks (most around a focus keyphrase), and 6 readability checks including Flesch reading ease (English).
* Every finding says what was found and what to do about it. There is no score: the checks are writing guidance, not a ranking promise.

= More =

* Breadcrumbs as a block, shortcode or theme function, matching the structured data.
* Redirects (301, 302, 307, 410) with loop protection, for administrators.
* Images without alternative text, listed in the media library.
* WooCommerce: product sharing tags, shop breadcrumbs, and cart/checkout pages kept out of search.

= Privacy and performance =

* No telemetry, no tracking, no calls to external services, no cookies.
* Measured, not claimed: on normal pages SEOEarth adds no extra database queries.
* Accessible: results are always stated in words, never by color alone.

If another SEO plugin is active, SEOEarth stops printing social tags and structured data and says so, so nothing is duplicated.

== Installation ==

1. Install SEOEarth from the Plugins screen (search for "SEOEarth"), or upload the `seoearth` folder to `/wp-content/plugins/`.
2. Activate the plugin.
3. Go to **SEOEarth** in the admin menu to check the site identity (organization or person, logo), title separator and defaults. Everything works with the defaults.
4. Edit any post and open the **SEOEarth** sidebar from the toolbar to see the preview and analysis.

== Frequently Asked Questions ==

= Does SEOEarth replace the WordPress sitemap? =

No. It improves the sitemap WordPress already provides at `/wp-sitemap.xml`, so it stays compatible with everything that expects it.

= Will SEOEarth conflict with another SEO plugin? =

Running two SEO plugins is not recommended. If Yoast SEO, Rank Math, All in One SEO, SEOPress, The SEO Framework or Slim SEO is active, SEOEarth does not print social tags or structured data, and shows a notice on its settings screen.

= Does the analysis guarantee better rankings? =

No. The checks reflect common, documented writing and on-page practices. Nobody can guarantee rankings, which is why SEOEarth shows findings rather than a score.

= Which languages does the readability analysis support? =

Sentence length, paragraph length and subheading checks work for every language. Passive voice, transition words and reading ease are English-only for now.

= How do I show breadcrumbs? =

Add the **Breadcrumbs** block to a template or post, use the `[seoearth_breadcrumbs]` shortcode, or call `seoearth_breadcrumbs()` in a theme template.

= What happens to my data if I delete the plugin? =

By default your settings and SEO fields are kept, so reinstalling restores them. To remove everything, tick "Remove all SEOEarth data when the plugin is deleted" under SEOEarth → Advanced before deleting. Your posts and pages are never deleted.

== Privacy ==

SEOEarth does not collect, store or send any personal data about visitors or users. It sets no cookies, loads nothing from third-party servers and makes no outbound requests. It stores only settings and the SEO fields you enter for your content (titles, descriptions, keyphrases, social fields, robots settings, redirects).

== Changelog ==

= 1.0.0 =
* First release: search appearance, canonical and robots, sitemap improvements, social tags, structured data, SEO and readability analysis, block editor sidebar and Classic Editor box, breadcrumbs, image alt text report, redirects and WooCommerce support.
