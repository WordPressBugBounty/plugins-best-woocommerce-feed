# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Plugin Overview

**Product Feed Manager for WooCommerce** — generates product feeds (XML, CSV, TSV, TXT) for 180+ merchant platforms (Google Shopping, Facebook Catalog, Yandex, eBay, etc.). Feeds are stored as WordPress custom post types and can be regenerated on a schedule.

- Main entry point: `rex-product-feed.php`
- Text domain: `rex-product-feed`
- Key constant prefix: `WPFM_`
- Free tier limit: 200 products (`WPFM_FREE_MAX_PRODUCT_LIMIT`)

## Development Setup

```bash
# Install dependencies
npm install
composer install

# Regenerate autoloader after adding new classes
composer dump-autoload
```

## Testing

Requires Docker running first.

```bash
# First time only: start WordPress environment and install test dependencies
npm run wp-env start
npm run composer

# Run PHPUnit tests
npm run phpunit

# Generate coverage report
npm run test:coverage
```

Tests live in `tests/unit-test/` (pattern: `test-*.php`). PHPUnit config: `phpunit.xml.dist`. The WordPress test environment runs on port 7777 (app) and 7771 (tests) via `@wordpress/env` (Docker).

## Linting

```bash
# PHP Code Sniffer (runs automatically on staged files via Husky pre-commit hook)
vendor/bin/phpcs

# Fix auto-fixable issues
vendor/bin/phpcbf
```

PHPCS config is in `phpcs.xml`. Standards: WordPress, WordPress-Extra, PHPCompatibility (PHP 7.4+), VIPWordPress. Note: `admin/`, `includes/`, `public/`, and `tests/` directories are currently excluded from PHPCS checks — only new/moved code outside these directories is checked.

## Build & Distribution

```bash
# Build distribution package (output to /build/)
npx grunt
```

Grunt tasks (config in `Gruntfile.js`): copies files, strips dev dependencies, creates a ZIP for distribution.

## Architecture

### WordPress Plugin Boilerplate Pattern

The plugin uses a centralized hook loader pattern:

1. `rex-product-feed.php` — bootstraps the plugin, checks WooCommerce dependency, defines constants
2. `includes/class-rex-product-feed.php` — main orchestrator; instantiates the loader, admin, and public classes
3. `includes/class-rex-product-feed-loader.php` — accumulates all `add_action()` / `add_filter()` calls and runs them via `run()`
4. `admin/class-rex-product-feed-admin.php` — registers admin hooks; delegates to specialized subsystems

### Feed Lifecycle (Data Flow)

1. User creates a feed via the `product-feed` CPT admin UI (`admin/class-rex-product-feed-cpt.php`)
2. Product attributes are mapped to merchant fields (`admin/class-rex-feed-attributes.php`)
3. Feed generation is triggered (manual or via cron scheduler `admin/class-rex-feed-scheduler.php`)
4. `admin/class-rex-product-feed-factory.php` instantiates the appropriate merchant-specific class from `admin/feed/`
5. `admin/class-rex-product-data-retriever.php` fetches WooCommerce products via `WP_Query`
6. Data flows through transformation filters, then the merchant class formats it
7. Feed file is written to the WordPress uploads directory and a public URL is exposed

### Key Subsystems

| Subsystem | Location | Purpose |
|---|---|---|
| Feed generators | `admin/feed/` | 75+ merchant-specific classes, each extending `abstract-rex-product-feed-generator.php` |
| Feed templates | `admin/feed-templates/` | JSON configs defining default attribute mappings per merchant |
| Feed validators | `admin/feed-validator/` | Platform validators (Google, Facebook, TikTok, etc.), instantiated via `Rex_Feed_Validator_Factory` |
| API integrations | `admin/api/` | Google Merchant Center API, category mappings |
| AJAX handlers | `admin/class-rex-product-feed-ajax.php` | All admin AJAX endpoints (`wp_ajax_*`) |
| Setup wizard | `includes/class-rex-product-feed-setup-wizard.php` | Onboarding flow for new users |
| Scheduler | `admin/class-rex-feed-scheduler.php` | Cron-based feed regeneration (hourly/daily/weekly/custom) |

### Autoloading & Namespaces

- Composer classmap autoloading covers `admin/` and `includes/`
- Most classes use the global namespace (legacy WordPress boilerplate convention)
- Newer classes use the `RexTheme\` namespace (e.g., `RexTheme\RexProductFeedManager\Tracking\Tracker`)
- Core classes are manually `require`d in the main plugin file before the autoloader

### Extension Hooks

```php
// Actions
do_action('wpfm_before_product_loop', $config);
do_action('wpfm_after_product_loop', $config);
do_action('wpfm_feed_completed', $feed_id);

// Filters
apply_filters('wpfm_product_data', $product_data, $product);
apply_filters('wpfm_feed_content', $content, $feed_id);
apply_filters('wpfm_attribute_value', $value, $attribute, $product);
```

### Adding a New Merchant Feed

1. Create a class in `admin/feed/` extending `abstract-rex-product-feed-generator.php`
2. Register the merchant in `admin/class-rex-product-feed-merchants.php`
3. Add a feed template JSON in `admin/feed-templates/`
4. Register the class in `admin/class-rex-product-feed-factory.php`
5. Run `composer dump-autoload`
