<?php
/**
 * Sample review seeder for testing AI review response functionality.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\CLI;

/**
 * Sample review seeder class for creating test data.
 */
class SampleReviewSeeder {

	/**
	 * Sample products data.
	 *
	 * @var array
	 */
	private $products = array(
		array(
			'name'        => 'Premium Wireless Headphones',
			'description' => 'High-quality wireless headphones with noise cancellation, 30-hour battery life, and premium sound quality. Perfect for music lovers and professionals.',
			'price'       => 299.99,
		),
		array(
			'name'        => 'Smart Fitness Tracker',
			'description' => 'Advanced fitness tracker with heart rate monitoring, sleep tracking, GPS, and 7-day battery life. Waterproof design for all activities.',
			'price'       => 199.99,
		),
		array(
			'name'        => 'Organic Coffee Beans',
			'description' => 'Premium organic coffee beans sourced from sustainable farms. Medium roast with notes of chocolate and caramel. Perfect for morning brew.',
			'price'       => 24.99,
		),
		array(
			'name'        => 'Bluetooth Speaker',
			'description' => 'Portable Bluetooth speaker with 360-degree sound, waterproof design, and 12-hour battery life. Perfect for outdoor adventures.',
			'price'       => 89.99,
		),
		array(
			'name'        => 'Luxury Watch',
			'description' => 'Elegant luxury watch with Swiss movement, sapphire crystal, and genuine leather strap. Timeless design for any occasion.',
			'price'       => 599.99,
		),
		array(
			'name'        => 'Gaming Mouse',
			'description' => 'High-precision gaming mouse with customizable RGB lighting, programmable buttons, and ultra-fast response time.',
			'price'       => 79.99,
		),
		array(
			'name'        => 'Yoga Mat',
			'description' => 'Premium non-slip yoga mat made from eco-friendly materials. Extra thick for comfort and durability during all yoga practices.',
			'price'       => 49.99,
		),
	);

	/**
	 * Sample reviews data mapped to template types.
	 *
	 * @var array
	 */
	private $reviews = array(
		'default'                  => array(
			'rating'  => 4,
			'comment' => 'Good product overall. Works as expected and arrived on time. Would recommend to others looking for something reliable.',
			'author'  => 'Sarah Johnson',
		),
		'enthusiastic_five_star'   => array(
			'rating'  => 5,
			'comment' => 'ABSOLUTELY AMAZING! This exceeded all my expectations! The quality is incredible and the customer service was outstanding. I\'ve already told all my friends about this product. Worth every penny and more!',
			'author'  => 'Mike Chen',
		),
		'positive_with_critique'   => array(
			'rating'  => 4,
			'comment' => 'Really love this product! The quality is excellent and it works perfectly. My only suggestion would be to make the instructions a bit clearer for setup. Other than that, it\'s fantastic!',
			'author'  => 'Emily Rodriguez',
		),
		'product_misunderstanding' => array(
			'rating'  => 2,
			'comment' => 'I thought this would work with my iPhone but it doesn\'t seem to connect properly. The description said it was compatible with all devices but I\'m having trouble. Maybe I\'m doing something wrong?',
			'author'  => 'David Kim',
		),
		'defective_product'        => array(
			'rating'  => 1,
			'comment' => 'Very disappointed. The product arrived damaged and doesn\'t work at all. The power button is stuck and the screen is cracked. This is clearly a manufacturing defect. I need a replacement immediately.',
			'author'  => 'Lisa Thompson',
		),
		'shipping_issue'           => array(
			'rating'  => 3,
			'comment' => 'The product itself is fine, but the shipping was terrible. It took 3 weeks to arrive when it was supposed to be 2-day delivery. The tracking information was never updated. Very frustrating experience.',
			'author'  => 'Robert Wilson',
		),
		'value_price_concern'      => array(
			'rating'  => 3,
			'comment' => 'The product is okay but I\'m not sure it\'s worth the high price. It works fine but I expected more features for what I paid. There are cheaper alternatives that seem to do the same thing.',
			'author'  => 'Jennifer Brown',
		),
	);

	/**
	 * Run the seeding process.
	 *
	 * @param bool $force Force seeding even if reviews already exist.
	 * @return array Result array with success status and data.
	 */
	public function seed( bool $force = false ): array {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array(
				'success' => false,
				'message' => 'WooCommerce must be active to run this seeding script.',
			);
		}

		// Check if reviews already exist and force is not set.
		if ( ! $force && $this->reviews_exist() ) {
			return array(
				'success' => false,
				'message' => 'Sample reviews already exist. Use --force to recreate them.',
			);
		}

		$created_products = array();
		$created_reviews  = array();

		// Create products.
		foreach ( $this->products as $index => $product_data ) {
			$product_id         = $this->create_product( $product_data );
			$created_products[] = $product_id;
		}

		// Create reviews.
		$template_types = array_keys( $this->reviews );

		foreach ( $template_types as $index => $template_type ) {
			$product_id   = $created_products[ $index ];
			$review_data  = $this->reviews[ $template_type ];
			$product_name = $this->products[ $index ]['name'];

			$review_id         = $this->create_review( $product_id, $review_data );
			$created_reviews[] = array(
				'review_id'     => $review_id,
				'product_id'    => $product_id,
				'product_name'  => $product_name,
				'template_type' => $template_type,
				'rating'        => $review_data['rating'],
				'comment'       => $review_data['comment'],
				'author'        => $review_data['author'],
			);
		}

		return array(
			'success'          => true,
			'products_created' => count( $created_products ),
			'reviews_created'  => count( $created_reviews ),
			'reviews'          => $created_reviews,
		);
	}

	/**
	 * Check if sample reviews already exist.
	 *
	 * @return bool True if reviews exist, false otherwise.
	 */
	private function reviews_exist(): bool {
		$existing_reviews = get_comments(
			array(
				'type'   => 'review',
				'status' => 'approve',
				'number' => 1,
				'count'  => true,
			)
		);

		return $existing_reviews > 0;
	}

	/**
	 * Create a WooCommerce product.
	 *
	 * @param array $product_data Product data.
	 * @return int Product ID.
	 */
	private function create_product( array $product_data ): int {
		$product = new \WC_Product_Simple();
		$product->set_name( $product_data['name'] );
		$product->set_description( $product_data['description'] );
		$product->set_short_description( substr( $product_data['description'], 0, 100 ) . '...' );
		$product->set_regular_price( $product_data['price'] );
		$product->set_price( $product_data['price'] );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_featured( false );
		$product->set_manage_stock( true );
		$product->set_stock_quantity( 100 );
		$product->set_stock_status( 'instock' );

		// Set product categories.
		$product->set_category_ids( array( $this->get_or_create_category( 'Electronics' ) ) );

		return $product->save();
	}

	/**
	 * Get or create a product category.
	 *
	 * @param string $category_name Category name.
	 * @return int Category ID.
	 */
	private function get_or_create_category( string $category_name ): int {
		$term = get_term_by( 'name', $category_name, 'product_cat' );

		if ( $term ) {
			return $term->term_id;
		}

		$result = wp_insert_term( $category_name, 'product_cat' );

		if ( is_wp_error( $result ) ) {
			return 0;
		}

		return $result['term_id'];
	}

	/**
	 * Create a product review.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $review_data Review data.
	 * @return int Review (comment) ID.
	 */
	private function create_review( int $product_id, array $review_data ): int {
		$comment_data = array(
			'comment_post_ID'      => $product_id,
			'comment_author'       => $review_data['author'],
			'comment_author_email' => strtolower( str_replace( ' ', '.', $review_data['author'] ) ) . '@example.com',
			'comment_content'      => $review_data['comment'],
			'comment_type'         => 'review',
			'comment_approved'     => 1,
			'comment_date'         => current_time( 'mysql' ),
			'comment_date_gmt'     => current_time( 'mysql', 1 ),
		);

		$comment_id = wp_insert_comment( $comment_data );

		if ( $comment_id ) {
			// Add rating meta.
			update_comment_meta( $comment_id, 'rating', $review_data['rating'] );
		}

		return $comment_id;
	}
}
