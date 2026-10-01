# Structured data (schema.org)

ShubhamTiwari SEO Tools adds one JSON-LD block to every page. It describes the site, the page and — on posts — the article and its author, as a single connected `@graph`. Search engines use it to understand the page; it does not guarantee rich results.

## What is printed

| Node | When | Main values |
|---|---|---|
| `Organization` or `Person` | Every page | *Site identity* settings: name (site title if empty), logo/image |
| `WebSite` | Every page | Site URL, name, tagline, language, site search (`SearchAction`) |
| `WebPage` / `CollectionPage` / `ProfilePage` | Pages with a canonical URL | SEO title, meta description, dates (single items), image, breadcrumb |
| `ImageObject` | Featured image present | URL, width, height, alt text as caption |
| `BreadcrumbList` | Anything below the homepage | Home → first category and its parents (posts), parent pages (pages), post type archive (other types) → current page |
| `BlogPosting` (posts) / `Article` (other post types) | Single posts and CPT items; never pages or media | Headline, description, dates, word count, categories, tags, publisher, author |
| `Person` (author) | With an article | Display name, author archive URL |

- Search results, 404 pages and noindex pages have no canonical URL, so they get only the `Organization`/`Person` and `WebSite` nodes.
- Password-protected posts expose no description, word count or image.
- The "Uncategorized" category is never listed as an article section.

## Settings

*SEO Tools → Structured data → Add structured data* (default on). Publisher values come from *Site identity* (*This website represents*, name, logo).

If another SEO plugin that prints structured data is active (Yoast SEO, Rank Math, All in One SEO, SEOPress, The SEO Framework, Slim SEO), ShubhamTiwari SEO Tools prints none and says so on its settings screen.

## Extending

```php
add_filter( 'stseo_schema_pieces', function ( $pieces ) {
	$pieces['faq'] = new My_FAQ_Piece(); // implements ShubhamTiwariSeoTools\Schema\Piece
	unset( $pieces['breadcrumb'] );      // references to it are dropped automatically
	return $pieces;
} );
```

Filters: `stseo_schema_output_enabled`, `stseo_schema_conflict`, `stseo_schema_pieces`, `stseo_schema_graph`, `stseo_schema_article_type`, `stseo_schema_search_action`, `stseo_breadcrumb_trail`.

## Cost (measured, Phase 8)

Single post (with a nested category), homepage and category archive: 0 extra database queries. Output is 1–2 KB per page.
