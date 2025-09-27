# WC AI Review Responder - Project Roadmap & Architecture

## Project Overview
**Plugin Name:** WC AI Review Responder  
**Goal:** AI-powered response generation for WooCommerce product reviews  
**Timeline:** 2-Day MVP  
**Tech Stack:** PHP, WordPress, WooCommerce, Gemini SDK, JavaScript/jQuery

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

### Day 1: Core Infrastructure (8 hours)

#### 1. Plugin Foundation & Exception System (2 hours)
- Basic plugin structure with Composer
- Custom exception classes:
  - `InvalidReviewException` (missing data)
  - `AI_Response_Failure` (API errors with debug info)
  - `InvalidArgumentsException` (security/permissions)
- .env file setup for API keys

#### 2. Review Data Handler (3 hours)
- Extract review data from `wp_comments` + `wp_commentmeta`
- Validate required fields (rating + comment)
- Product context extraction
- Edge case handling

#### 3. Gemini API Client (3 hours)
- SDK integration with error mapping
- Prompt building system
- Response validation + sanitization
- Rate limiting + cost control

### Day 2: UI/UX & Polish (8 hours)

#### 4. Admin UI Integration (4 hours)
- "Generate AI Response" button in review actions
- AJAX handlers with nonce verification
- Loading states + response insertion
- Regenerate functionality

#### 5. Error Handling & Testing (4 hours)
- User-friendly error modal system
- Edge case testing (incomplete reviews, API failures)
- Demo preparation + documentation
- UI polish for presentation

## Post-MVP Roadmap

### P1 Enhancements
- Support rating-only reviews (no comments)
- Support comment-only reviews (no ratings)
- Admin settings page for API configuration
- Brand voice customization (professional/friendly/concise)

### P2 Features
- Bulk response generation
- Response template library
- Multi-language support
- Third-party review plugin integration (Yotpo, WP Product Review)

### P3 Advanced
- Sentiment analysis dashboard
- Response analytics + performance tracking
- Automated suggestion system
- Competitor benchmarking

### P4 Enterprise
- Team collaboration workflows
- Response approval queues
- Custom prompts per product category
- A/B testing framework

## Plugin Architecture

### File Structure
```
/wc-ai-review-responder/
├── wc-ai-review-responder.php          # Main plugin file
├── composer.json                        # Dependencies (Gemini SDK)
├── .env.example                         # API key template
├── includes/
│   ├── class-ai-client.php             # Gemini API handler
│   ├── class-review-handler.php        # WC data extraction + validation
│   ├── class-ajax-handler.php          # AJAX processing
│   └── exceptions/
│       ├── class-invalid-review-exception.php
│       ├── class-ai-response-failure.php
│       └── class-invalid-arguments-exception.php
├── assets/
│   ├── js/
│   │   └── admin.js                    # UI interactions + error modals
│   └── css/
│       └── admin.css                   # Loading states + modal styles
└── languages/                          # Translation files
    └── wc-ai-review-responder.pot
```

### Class Responsibilities

#### ReviewHandler
- Extracts review data from WordPress database
- Validates required fields (rating + comment)
- Returns product context for prompts
- Throws `InvalidReviewException` for incomplete data

#### AIClient 
- Manages Gemini API connection
- Builds context-aware prompts
- Handles API errors + rate limiting
- Validates + sanitizes AI responses
- Throws `AI_Response_Failure` for API issues

#### AjaxHandler
- Processes "Generate AI Response" requests
- Validates nonces + user capabilities
- Coordinates ReviewHandler + AIClient
- Returns JSON responses for frontend

### Data Flow
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

### Error Handling Strategy
- **Invalid reviews:** Specific messages about missing data
- **API failures:** User-friendly messages + debug info for admins
- **Security issues:** Generic error messages
- **All errors:** Modal display with auto-dismiss

### Security Implementation
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
add_action('wp_ajax_generate_ai_response', $callback);
```

### Frontend Components
- **Button:** "Generate AI Response" in comment actions
- **Loading State:** Disabled button + spinner
- **Error Modal:** Dismissible notice with debug info
- **Success:** Response inserted into reply textarea

---

*This document will be updated as development progresses. Last updated: 2024-12-19*