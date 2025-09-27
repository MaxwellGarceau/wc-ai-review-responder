# WC AI Review Responder - File Structure Mapping

## Project Overview
This is a WordPress/WooCommerce plugin that provides AI-powered review response functionality. The project uses a hybrid architecture with both PHP backend and JavaScript frontend components.

## Root Directory Structure

```
wc-ai-review-responder/
├── 📄 wc-ai-review-responder.php     # Main plugin file (entry point)
├── 📄 block.json                     # WordPress block configuration
├── 📄 composer.json                  # PHP dependencies and autoloading
├── 📄 composer.lock                  # Locked PHP dependency versions
├── 📄 package.json                   # Node.js dependencies and scripts
├── 📄 package-lock.json              # Locked Node.js dependency versions
├── 📄 webpack.config.js              # Webpack build configuration
├── 📄 README.md                      # Project documentation
├── 📁 .github/                       # GitHub configuration
│   └── 📄 pull_request_template.md   # PR template
├── 📁 build/                         # Compiled/built assets
├── 📁 docs/                          # Documentation
├── 📁 includes/                      # PHP source code
├── 📁 languages/                     # Internationalization files
├── 📁 node_modules/                  # Node.js dependencies
├── 📁 src/                           # JavaScript/SCSS source code
├── 📁 tests/                         # PHPUnit tests
└── 📁 vendor/                        # PHP dependencies
```

## Configuration Files

### Hidden Configuration Files
```
├── 📄 .wp-env.json                   # WordPress development environment config
├── 📄 .nvmrc                         # Node.js version specification
├── 📄 .gitignore                     # Git ignore patterns
├── 📄 .prettierrc.json               # Prettier code formatting config
├── 📄 .editorconfig                  # Editor configuration
└── 📄 .eslintrc.js                   # ESLint JavaScript linting config
```

## Source Code Structure

### PHP Backend (`includes/`)
```
includes/
└── 📁 admin/
    └── 📄 setup.php                  # Admin setup and page registration
```

**Key PHP Classes:**
- `WcAiReviewResponder\Admin\Setup` - Handles admin page setup, script registration, and WooCommerce admin integration

### JavaScript Frontend (`src/`)
```
src/
├── 📄 index.js                       # Main React component and WooCommerce admin integration
└── 📄 index.scss                     # Styles for the admin interface
```

**Key JavaScript Components:**
- `MyExamplePage` - Main React component with WooCommerce UI components
- WooCommerce admin page integration via `addFilter`

### Built Assets (`build/`)
```
build/
├── 📄 index.js                       # Compiled JavaScript bundle
├── 📄 index.js.map                   # Source map for debugging
├── 📄 index.css                      # Compiled CSS bundle
├── 📄 index.css.map                  # CSS source map
├── 📄 index-rtl.css                  # Right-to-left language support
└── 📄 index.asset.php                # WordPress asset dependencies
```

## Dependencies

### PHP Dependencies (`vendor/`)
- **automattic/jetpack-autoloader** - WordPress autoloader
- **phpunit/phpunit** - Testing framework
- **doctrine/instantiator** - Object instantiation utilities
- **nikic/php-parser** - PHP code parser
- **phar-io/manifest** - PHAR manifest handling
- **sebastian/** - PHPUnit testing utilities
- **theseer/tokenizer** - Token parsing utilities

### Node.js Dependencies
- **@woocommerce/components** - WooCommerce UI components
- **@wordpress/hooks** - WordPress hook system
- **@wordpress/i18n** - Internationalization
- **@wordpress/scripts** - WordPress build tools
- **@woocommerce/eslint-plugin** - WooCommerce ESLint rules

## Testing Structure

### PHP Tests (`tests/`)
```
tests/
└── 📄 Test.php                       # Basic PHPUnit test class
```

## Internationalization (`languages/`)
```
languages/
└── 📄 woo-plugin-setup.pot           # Translation template file
```

## Key Features & Architecture

### Plugin Architecture
1. **Main Plugin File**: `wc-ai-review-responder.php`
   - Plugin header and metadata
   - WooCommerce dependency check
   - Singleton pattern implementation
   - Autoloader initialization

2. **Admin Integration**: `includes/admin/setup.php`
   - WooCommerce admin page registration
   - Script and style enqueuing
   - Admin menu integration

3. **Frontend Components**: `src/index.js`
   - React-based admin interface
   - WooCommerce component integration
   - WordPress hooks and filters

### Build System
- **Webpack**: Bundles JavaScript and SCSS
- **WordPress Scripts**: Handles WordPress-specific build tasks
- **Composer**: Manages PHP dependencies and autoloading

### Development Workflow
- **wp-env**: Local WordPress development environment
- **ESLint/Prettier**: Code quality and formatting
- **PHPUnit**: PHP testing framework
- **GitHub Actions**: CI/CD pipeline (configured via `.github/`)

## File Purposes Summary

| File/Directory | Purpose |
|----------------|---------|
| `wc-ai-review-responder.php` | Main plugin entry point, dependency checks, initialization |
| `block.json` | WordPress block configuration for Gutenberg editor |
| `composer.json` | PHP dependency management and PSR-4 autoloading |
| `package.json` | Node.js dependencies and build scripts |
| `webpack.config.js` | Asset bundling configuration |
| `includes/admin/setup.php` | Admin interface setup and WooCommerce integration |
| `src/index.js` | React frontend components and admin page logic |
| `src/index.scss` | Frontend styling |
| `build/` | Compiled assets ready for production |
| `tests/Test.php` | Basic PHPUnit test structure |
| `languages/` | Internationalization support |
| `vendor/` | PHP dependencies (Composer managed) |
| `node_modules/` | Node.js dependencies (npm managed) |

## Development Commands

### Available Scripts (from package.json)
- `npm run build` - Build production assets
- `npm run start` - Start development server
- `npm run dev` - Start WordPress development environment
- `npm run test` - Run PHP tests
- `npm run lint:js` - Lint JavaScript code
- `npm run lint:css` - Lint CSS code
- `npm run format` - Format code with Prettier

This structure follows WordPress plugin development best practices with modern tooling for both PHP and JavaScript development.
