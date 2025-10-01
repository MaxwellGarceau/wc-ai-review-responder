# Roadmap Archive

This document contains archived sections from the original MVP roadmap that are no longer current but preserved for historical reference.

## Project Overview
**Plugin Name:** WC AI Review Responder  
**Goal:** AI-powered response generation for WooCommerce product reviews  
**Timeline:** 2-Day MVP  
**Tech Stack:** PHP, WordPress, WooCommerce, Gemini SDK, Modern JavaScript (ES6+)

## MVP Requirements (V1)
### Core Functionality
- "Generate AI Response" button in WooCommerce reviews admin
- Only processes reviews with BOTH rating AND comment
- Uses Gemini API via SDK with .env API key management
- Response inserts into reply textarea for manual approval
- Robust error handling with user-friendly messages

### User Flow
1. Admin views reviews at `/wp-admin/edit-comments.php?comment_type=review`
2. Clicks "Generate AI Response" button next to eligible review
3. System validates review has rating + comment
4. AI generates context-aware response using product info
5. Response inserts into reply box for editing/approval
6. User can regenerate if unsatisfied

### Technical Constraints
- **V1 Limitation:** Requires both rating AND comment
- **API:** Gemini SDK only (OpenAI support post-MVP)
- **Security:** WordPress nonces + capability checks
- **Error Handling:** Custom exceptions with debug info

## 2-Day Implementation Plan

### Day 1: Core Infrastructure (9 hours)

#### 1. Plugin Base & Exceptions (2 hours)
- Basic plugin structure with Composer
- Custom exception classes:
  - `InvalidReviewException` (missing data)
  - `AI_Response_Failure` (API errors with debug info)
  - `InvalidArgumentsException` (security/permissions)
- .env file setup for API keys

#### 2. WP-CLI Testing Command (1 hour)
- Create custom WP-CLI command: `wp ai-review test <review_id>`
- Command will use ReviewHandler, PromptBuilder, AIClient, and ResponseValidator classes
- Output formatted results with success/error messages
- Test all edge cases via command line:
  - Valid reviews with rating + comment
  - Reviews missing ratings
  - Reviews missing comments  
  - API failure scenarios
  - Non-existent reviews

#### 3. Review Data Handler (3 hours)
- Extract review data from `wp_comments` + `wp_commentmeta`
- Validate required fields (rating + comment)
- Product context extraction
- Edge case handling

#### 4. Gemini API Client (3 hours)
- SDK integration with error mapping
- Prompt building system
- Response validation + sanitization
- Rate limiting + cost control

##### **CLI Command Features**
```bash
# Basic test
wp ai-review test 123

# Test with debug output
wp ai-review test 123 --debug

# Create and test sample data
wp ai-review test-sample
```

##### **Implementation Details**
```php
// File: includes/class-cli-tester.php
class WC_AI_Review_CLI_Tester {
    public function test_single($args, $assoc_args) {
        // Test one review with full output
    }
    
    public function test_sample($args, $assoc_args) {
        // Create and test sample review data
    }
}

// Register commands
WP_CLI::add_command('ai-review test', ['WC_AI_Review_CLI_Tester', 'test_single']);
WP_CLI::add_command('ai-review test-sample', ['WC_AI_Review_CLI_Tester', 'test_sample']);
```

##### **Benefits**
- **Rapid Testing**: Test backend without UI dependencies
- **Automated Testing**: Scriptable for continuous testing
- **Debug Friendly**: Full error details and stack traces
- **Development Speed**: Faster feedback loop during backend development

### Day 2: UI/UX & Polish (8 hours)

#### 5. Admin UI Integration (4 hours)
- "Generate AI Response" button in review actions
- AJAX handlers with nonce verification
- Loading states + response insertion
- Regenerate functionality

#### 6. Error Handling & Testing (4 hours)
- User-friendly error modal system
- Edge case testing (incomplete reviews, API failures)
- Demo preparation + documentation
- UI polish for presentation