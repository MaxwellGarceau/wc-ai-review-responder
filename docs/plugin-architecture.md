# Plugin Architecture

## Table of Contents

- [AI Response Templates](#ai-response-templates)
- [Data Flow](#data-flow)
- [Testing Strategy](#testing-strategy)
- [Technical Specifications](#technical-specifications)
  - [Localization](#localization)
  - [Configuration](#configuration)
  - [Code Style & Quality](#code-style--quality)
  - [Dependency Injection (DI) in PHP](#dependency-injection-di-in-php)
  - [Error Handling](#error-handling)
  - [Security](#security)
  - [Database Schema Integration](#database-schema-integration)
  - [API Integration](#api-integration)
  - [WordPress Hooks](#wordpress-hooks)
  - [Frontend Components](#frontend-components)

## AI Response Templates
These are like fill-in-the-blank forms that the AI uses to generate consistent, mood-appropriate replies. The template system allows for structured, context-aware responses that maintain brand voice and handle different review scenarios appropriately.

For a comprehensive explanation of how AI response templates and the mood system work—including how templates are structured, how moods are applied, and how to extend or validate them—see [AI Response Templates and Mood System](ai-response-templates.md).

## Data Flow

For detailed information about the plugin's data flow, see [Data Flow Documentation](data-flow.md).

## Testing Strategy

### Frontend Testing (JavaScript/TypeScript)
- **Framework:** Jest unit tests
- **Mocking:** API clients and external dependencies
- **Coverage:** Component behavior, user interactions, and error handling
- **Location:** `tests/js/` directory

### Backend Testing (PHP)
- **Framework:** PHPUnit with wp-env testing environment
- **Environment:** Full WordPress and WooCommerce functionality available
- **Approach:** Integration tests rather than pure unit tests
- **Coverage:** All classes, CLI commands, and WordPress integrations
- **Location:** `tests/php/` directory

### End-to-End Testing
- **Approach:** Manual testing using CLI commands
- **Seeding:** `wp ai-review-seed seed` to create sample reviews
- **Testing:** `wp ai-review test <review_id>` to exercise full backend logic
- **Coverage:** Complete AI response generation pipeline from review data to final response

### Test Execution
```bash
# Frontend tests
npm run test:js

# Backend tests  
npm run test:php

# All tests
npm run test

# E2E testing workflow
wp ai-review-seed seed          # Create sample data
wp ai-review test <review_id>   # Test AI response generation
```

## Technical Specifications

### Localization
Using WP's native localization system we ensures that all user-facing text can be translated, making the plugin accessible to a global audience. This system supports multiple languages and cultural contexts for international merchants.

### Configuration
Centralizes environment variables and settings, similar to a control panel for the plugin. This includes API keys, model preferences, and behavioral settings that merchants can customize.

### Code Style & Quality
Enforces consistent code formatting and review processes, which is like following a recipe to ensure every dish (feature) is made the same way. This maintains code readability and reduces bugs.

### Dependency Injection (DI) in PHP

The plugin uses Dependency Injection (DI) to manage and provide dependencies between classes, improving modularity, testability, and maintainability. Instead of classes creating their own dependencies, required objects are passed in (injected) via constructors or method parameters. This approach decouples components and makes it easier to swap implementations or mock dependencies for testing.

Classes using interfaces or dependencies with constructor params are created in the `ContainerFactory.php` file and inserted deliberately.

### Error Handling
Standardizes how problems are reported and managed, making debugging and support easier. The system provides user-friendly error messages while maintaining detailed logging for developers.
- **Invalid reviews:** Specific messages about missing data
- **API failures:** User-friendly messages + debug info for admins
- **Security issues:** Generic error messages
- **All errors:** Modal display with auto-dismiss

### Security
Outlines best practices to keep user data and the plugin itself safe from threats. This includes input validation, output escaping, and secure API key management.
- WordPress nonces for all AJAX requests
- `current_user_can('moderate_comments')` check
- Input sanitization + output escaping
- API key secured via .env (not in codebase)

### Database Schema Integration
- **Reviews:** `wp_comments` (comment_type = 'review')
- **Ratings:** `wp_commentmeta` (meta_key = 'rating')
- **Products:** `wp_posts` (post_type = 'product')

### API Integration
- **Primary:** Google Gemini via SDK
- **Fallback:** Template-based responses (if API fails)
- **Caching:** Transient API for 24 hours to reduce costs

### WordPress Hooks
```php
// Admin UI
add_filter('comment_row_actions', $callback, 10, 2);
add_action('admin_enqueue_scripts', $callback);

// AJAX handlers
// Prefer using the class constant to avoid string duplication:
// add_action('wp_ajax_' . AjaxHandler::ACTION_GENERATE_AI_RESPONSE, $callback);
add_action('wp_ajax_generate_ai_response', $callback);
```

### Frontend Components
- **Button:** "Generate AI Response" in comment actions
- **Loading State:** Disabled button + spinner
- **Error Modal:** Dismissible notice with debug info
- **Success:** Response inserted into reply textarea
