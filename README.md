# Wc Ai Review Responder

A WooCommmerce Extension inspired by [Create Woo Extension](https://github.com/woocommerce/woocommerce/blob/trunk/packages/js/create-woo-extension/README.md).

## Getting Started

### Prerequisites

-   [NPM](https://www.npmjs.com/)
-   [Composer](https://getcomposer.org/download/)
-   [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)

### Installation and Build

```
npm install
npm run build
wp-env start
```

Visit the added page at http://localhost:8888/wp-admin/admin.php?page=wc-admin&path=%2Fexample.

## Linting (PHP CodeSniffer)

This project uses PHPCS with the WordPress and WooCommerce standards.

### Install dependencies

```
composer install
```

### Run linters

-   PHP:

```
npm run lint:php
```

-   Auto-fix PHP issues where possible:

```
npm run fix:php
```

PHPCS configuration lives in `phpcs.xml.dist` and includes:

-   `WooCommerce-Core`
-   `WordPress`

Excluded directories: `vendor/`, `node_modules/`, `build/`, `tests/`.

## Environment Variables

Create a `.env` file in the project root based on `.env.example`:

```
GEMINI_API_KEY=your_api_key_here
```

The plugin loads environment variables using `vlucas/phpdotenv`. Ensure `.env` is not committed (already ignored in `.gitignore`).

## Documentation

For detailed information about the project's architecture and patterns, see the `/docs` folder:

- [SCSS Architecture](docs/scss-architecture.md) - Layered SCSS architecture and patterns
