# Development

## Prerequisites
- PHP 7.4+ (8.x recommended locally) with `mbstring`, `xml`, `zip`
- Composer 2 (`brew install composer`)
- Node.js 20+ and npm
- Docker Desktop (running) — for the wp-env WordPress + MySQL environment

## Setup
```bash
composer install
npm install
npm run env:start      # WordPress at http://localhost:8888 (admin / password)
```

## Everyday commands
| Command | What it does |
|---|---|
| `composer lint` | `php -l` on every PHP file |
| `composer check-versions` | Fails if plugin header, `SEOEARTH_VERSION`, readme Stable tag and package.json disagree |
| `composer phpcs` / `composer phpcbf` | WordPress Coding Standards + PHP 7.4 compatibility / auto-fix |
| `composer phpstan` | Static analysis, level 6, with WordPress stubs |
| `composer test` | Unit tests (Brain Monkey, no WordPress) |
| `composer ci` | All of the above |
| `npm run test:php:integration` | Integration tests inside wp-env against real WordPress + MySQL |
| `npm run test:php:multisite` | Same integration suite, as a multisite network |
| `npm run lint:js` / `npm run test:js` | ESLint (WordPress config) / Jest |
| `npm run build` | Build the editor sidebar (`build/editor/`) and block scripts (`build/blocks/*/`) |
| `npm run env:stop` | Stop the wp-env containers |

## Tests
- `tests/Unit/` — pure logic, WordPress functions mocked with Brain Monkey. Fast; run on every change.
- `tests/Integration/` — real WordPress via the core test suite that wp-env provides at `/wordpress-phpunit`.
- Test WordPress 6.4 locally: `echo '{"core":"WordPress/WordPress#6.4"}' > .wp-env.override.json && npx wp-env start --update`.

## Coding rules
- WordPress Coding Standards; PSR-4 class files in `src/`.
- Every `phpcs:ignore` or config exclusion must carry a reason comment.
- No `@phpstan-ignore` without a reason.
- Sanitize on input, escape on output (late), nonce + `current_user_can()` for every state change.
- All user-facing strings use text domain `seoearth`.

## Git
- `main` is always releasable. Work on branches; merge via PR once CI is green.
