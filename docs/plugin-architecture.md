# Plugin Architecture

## Data Flow
```
Frontend (admin.js)
    ↓ AJAX request
AjaxHandler (nonce/capability validation)
    ↓
ReviewHandler (data extraction + validation)
    ↓
AIClient (prompt building + API call)
    ↓
Response validation + sanitization
    ↓
JSON response to frontend
    ↓
Insert into reply textarea
```

## Error Handling Strategy
- **Invalid reviews:** Specific messages about missing data
- **API failures:** User-friendly messages + debug info for admins
- **Security issues:** Generic error messages
- **All errors:** Modal display with auto-dismiss

## Security Implementation
- WordPress nonces for all AJAX requests
- `current_user_can('moderate_comments')` check
- Input sanitization + output escaping
- API key secured via .env (not in codebase)

## Technical Specifications

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
