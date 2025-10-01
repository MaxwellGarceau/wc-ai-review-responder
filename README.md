# Wc Ai Review Responder

AI generate a mood and template specific response to various types customer reviews.

## Getting Started

### Prerequisites

-   [NPM](https://www.npmjs.com/)
-   [Composer](https://getcomposer.org/download/)
-   [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)

### Installation and Build

```
npm install
composer install

npm run wp-env:start // Start wp-env/docker/DB
npm run wp-scripts:start // Start Webpack FE compile
```

Visit the added page at http://localhost:8888/wp-admin/admin.php?page=wc-admin&path=%2Fexample.

## Linting (PHP CodeSniffer)

This project uses PHPCS with the WordPress and WooCommerce standards.

### Linters

#### Lint
```
npm run lint:php
npm run lint:js
npm run lint:css
```

#### Fix
```
npm run fix:php
npm run lint:js -- --fix
npm run lint:css -- --fix
```

PHPCS configuration lives in `phpcs.xml.dist` and includes:

-   `WooCommerce-Core`
-   `WordPress`

Excluded directories: `vendor/`, `node_modules/`, `build/`, `tests/`.

## Environment Variables
Environment variables are for local dev only and will be phased out for production use eventually.

Create a `.env` file in the project root based on `.env.example`:

```
GEMINI_API_KEY=your_api_key_here
```

The plugin loads environment variables using `vlucas/phpdotenv`. Ensure `.env` is not committed (already ignored in `.gitignore`).

## Documentation

For detailed information about the project's architecture and patterns, see the `/docs` folder:

- [SCSS Architecture](docs/scss-architecture.md) - Layered SCSS architecture and patterns
