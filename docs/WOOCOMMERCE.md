# WooCommerce

When WooCommerce is active, SEOEarth adjusts a few things for shops automatically. There is nothing to configure. SEOEarth never changes WooCommerce's data or its own output; it only uses WooCommerce's documented filters.

| Where | What SEOEarth does |
|---|---|
| Cart, checkout, My account | Hidden from search engines (noindex, no canonical URL) and left out of the XML sitemap. These pages are different for every visitor and have nothing to rank for. |
| Product pages — sharing | `og:type` is `product`, plus `product:price:amount`, `product:price:currency` and `product:availability` (in stock / out of stock / available for order). The featured product image is used for sharing, as for any post. |
| Product pages — structured data | SEOEarth's graph describes the page as an `ItemPage` and does not add an Article. The `Product` data (price, stock, reviews) is left to WooCommerce, which already prints it. |
| Duplicate structured data | While SEOEarth prints its graph, WooCommerce's separate breadcrumb and website blocks are turned off, so each page has one breadcrumb list and one website description. If SEOEarth's structured data is off, WooCommerce's blocks stay. |
| Breadcrumbs | Home › Shop › product category (and its parents) › product. Category archives: Home › Shop › category. |
| Editor | Products get the SEOEarth sidebar/metabox like any other post type (title, description, keyphrase, social, analysis). |

The rest of SEOEarth works for products, product categories and product tags the same way as for posts: titles and descriptions (with templates per type under *Search appearance*), canonical URLs, robots settings, the XML sitemap and redirects.

Advanced WooCommerce features (product-specific schema editing, GTIN/brand fields, feeds) are not part of SEOEarth Free.
