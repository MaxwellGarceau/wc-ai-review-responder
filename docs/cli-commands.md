# CLI Commands

This document provides instructions for using the WP-CLI commands available in the WC AI Review Responder plugin.

## Available Commands

### 1. AI Review Testing (`wp ai-review`)

Test the AI review response generation functionality.

#### Usage
```bash
wp ai-review test <review_id>
```

#### Description
- Generates an AI response for a specific product review
- Validates review data and processes it through the AI pipeline
- Displays detailed output including prompts and responses
- Useful for testing and debugging AI functionality

#### Example
```bash
wp ai-review test 123
```

### 2. Database Seeding (`wp ai-review-seed`)

Create sample products and reviews for testing purposes.

#### Usage
```bash
wp ai-review-seed seed [--force]
```

#### Options
- `--force`: Force recreation of sample data even if reviews already exist

#### Description
- Creates 7 sample WooCommerce products with realistic data
- Generates 7 sample reviews covering all template types:
  - Default (4/5 stars)
  - Enthusiastic Five Star (5/5 stars)
  - Positive with Critique (4/5 stars)
  - Product Misunderstanding (2/5 stars)
  - Defective Product (1/5 stars)
  - Shipping Issue (3/5 stars)
  - Value Price Concern (3/5 stars)

#### Important Notes
- **Existing Reviews**: The command will fail if reviews already exist in the database
- **Force Override**: Use `--force` flag to recreate sample data when reviews exist
- **WooCommerce Required**: Command requires WooCommerce to be active

#### Examples
```bash
# Basic seeding
wp ai-review-seed seed

# Force recreation of existing sample data
wp ai-review-seed seed --force
```

#### NPM Scripts
For convenience, npm scripts are available:
```bash
npm run db:seed        # Basic seeding
npm run db:seed:force  # Force recreation
```

## Workflow Example

1. **Seed sample data**:
   ```bash
   wp ai-review-seed seed
   ```

2. **Test AI responses**:
   ```bash
   wp ai-review test 123  # Use review ID from seeding output
   ```

3. **Force recreate data if needed**:
   ```bash
   wp ai-review-seed seed --force
   ```

## Prerequisites

- WordPress installed and configured
- WooCommerce plugin active
- WP-CLI available
- AI Review Responder plugin installed
- Valid Gemini API key configured (for testing command)
