# Dependency & License Inventory

SEOEarth is GPL-2.0-or-later. This file lists every third-party component **shipped in the distributed plugin ZIP**, plus development-only tooling for transparency.

## Shipped in the plugin ZIP

| Library | Version | License | Source | GPL-compatible | Attribution required |
|---|---|---|---|---|---|
| _(none)_ | | | | | |

`build/editor/index.js` is compiled from our own `assets-src/`; WordPress packages (`@wordpress/*`) are referenced as external `wp.*` globals provided by WordPress core and are **not** bundled (see `build/editor/index.asset.php`). Re-verify this whenever a dependency is added.

## Development-only (NOT shipped)

| Tool | License | Purpose |
|---|---|---|
| PHPUnit, Brain Monkey, Yoast PHPUnit Polyfills | BSD-3 / MIT / BSD-3 | Tests |
| PHP_CodeSniffer, WPCS, PHPCompatibilityWP | BSD-3 / MIT / LGPL-3 | Coding standards |
| PHPStan, phpstan-wordpress, wordpress-stubs | MIT | Static analysis |
| @wordpress/scripts, @wordpress/env, TypeScript | GPL-2.0+ / Apache-2.0 | Build, lint, test environment |

Last audited: 2026-09-29 (Phase 1).
