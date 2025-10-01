# Database Seeding for Sample Reviews

This document explains how to use the WP-CLI seeding command to create sample reviews for testing the AI review response functionality.

## Overview

The seeding command creates:
- 7 sample WooCommerce products
- 7 sample reviews (one for each template type)
- Proper database relationships and metadata

## Template Types and Sample Reviews

| Template Type | Product | Rating | Review Scenario |
|---------------|---------|--------|-----------------|
| `default` | Premium Wireless Headphones | 4/5 | General positive review |
| `enthusiastic_five_star` | Smart Fitness Tracker | 5/5 | Very enthusiastic 5-star review |
| `positive_with_critique` | Organic Coffee Beans | 4/5 | Positive review with minor critique |
| `product_misunderstanding` | Bluetooth Speaker | 2/5 | Review showing product misunderstanding |
| `defective_product` | Luxury Watch | 1/5 | Review about defective product |
| `shipping_issue` | Gaming Mouse | 3/5 | Review about shipping problems |
| `value_price_concern` | Yoga Mat | 3/5 | Review questioning value for price |

## Usage

### Prerequisites

1. WordPress must be installed and configured
2. WooCommerce plugin must be active
3. The AI Review Responder plugin must be installed
4. WP-CLI must be available

### Running the Seeding Command

Use the WP-CLI command:

```bash
wp ai-review-seed seed
```

### Options

- `--force`: Force seeding even if reviews already exist

### Examples

```bash
# Basic seeding
wp ai-review-seed seed

# Force recreation of existing reviews
wp ai-review-seed seed --force
```

### Expected Output

The script will:
1. Create 7 sample products with descriptions and pricing
2. Create 7 sample reviews with appropriate ratings and comments
3. Display a summary of all created reviews
4. Provide WP-CLI commands to test each review

### Testing AI Responses

After seeding, you can test AI responses using the WP-CLI command:

```bash
# Test a specific review
wp ai-review test [REVIEW_ID]

# Example commands (IDs will vary)
wp ai-review test 123  # default template
wp ai-review test 124  # enthusiastic_five_star template
wp ai-review test 125  # positive_with_critique template
wp ai-review test 126  # product_misunderstanding template
wp ai-review test 127  # defective_product template
wp ai-review test 128  # shipping_issue template
wp ai-review test 129  # value_price_concern template
```

## Sample Review Content

### Default Template
- **Product**: Premium Wireless Headphones
- **Rating**: 4/5
- **Review**: "Good product overall. Works as expected and arrived on time. Would recommend to others looking for something reliable."

### Enthusiastic Five Star Template
- **Product**: Smart Fitness Tracker
- **Rating**: 5/5
- **Review**: "ABSOLUTELY AMAZING! This exceeded all my expectations! The quality is incredible and the customer service was outstanding. I've already told all my friends about this product. Worth every penny and more!"

### Positive With Critique Template
- **Product**: Organic Coffee Beans
- **Rating**: 4/5
- **Review**: "Really love this product! The quality is excellent and it works perfectly. My only suggestion would be to make the instructions a bit clearer for setup. Other than that, it's fantastic!"

### Product Misunderstanding Template
- **Product**: Bluetooth Speaker
- **Rating**: 2/5
- **Review**: "I thought this would work with my iPhone but it doesn't seem to connect properly. The description said it was compatible with all devices but I'm having trouble. Maybe I'm doing something wrong?"

### Defective Product Template
- **Product**: Luxury Watch
- **Rating**: 1/5
- **Review**: "Very disappointed. The product arrived damaged and doesn't work at all. The power button is stuck and the screen is cracked. This is clearly a manufacturing defect. I need a replacement immediately."

### Shipping Issue Template
- **Product**: Gaming Mouse
- **Rating**: 3/5
- **Review**: "The product itself is fine, but the shipping was terrible. It took 3 weeks to arrive when it was supposed to be 2-day delivery. The tracking information was never updated. Very frustrating experience."

### Value Price Concern Template
- **Product**: Yoga Mat
- **Rating**: 3/5
- **Review**: "The product is okay but I'm not sure it's worth the high price. It works fine but I expected more features for what I paid. There are cheaper alternatives that seem to do the same thing."

## Database Schema

The script creates data in the following WordPress/WooCommerce tables:

- `wp_posts` - Product data (post_type = 'product')
- `wp_comments` - Review data (comment_type = 'review')
- `wp_commentmeta` - Rating data (meta_key = 'rating')
- `wp_terms` - Product categories
- `wp_term_taxonomy` - Category taxonomy
- `wp_term_relationships` - Product-category relationships

## Cleanup

To remove the seeded data, you can:

1. Delete the products from WooCommerce admin
2. Or run a cleanup script (not provided)

## Troubleshooting

### Common Issues

1. **"WooCommerce must be active" error**
   - Ensure WooCommerce plugin is installed and activated

2. **Database connection issues**
   - Verify WordPress configuration is correct
   - Check database credentials in wp-config.php

3. **Permission errors**
   - Ensure the script has proper file permissions
   - Run from the correct directory

### Verification

To verify the seeding worked correctly:

1. Check WooCommerce admin for the new products
2. Check Comments admin for the new reviews
3. Verify ratings are properly assigned
4. Test AI response generation with WP-CLI commands
