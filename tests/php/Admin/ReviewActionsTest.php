<?php
/**
 * Test the ReviewActions class.
 *
 * @package WcAiReviewResponder
 */

use WcAiReviewResponder\Admin\ReviewActions;

/**
 * Test the ReviewActions class.
 */
class ReviewActionsTest extends WP_UnitTestCase {

	/**
	 * Review actions instance.
	 *
	 * @var ReviewActions
	 */
	private $review_actions;

	/**
	 * Set up the test environment.
	 */
	public function setUp(): void {
		parent::setUp();
		$translations = $this->createMock( \WcAiReviewResponder\Localization\Translations::class );
		$translations->method( 'get_js_strings' )->willReturn( array() );
		$translations->method( 'get_php_strings' )->willReturn( array( 'generateAiResponse' => 'Generate AI Response' ) );
		$this->review_actions = new ReviewActions( $translations );
	}

	/**
	 * Tear down the test environment.
	 */
	public function tearDown(): void {
		parent::tearDown();
		// Reset enqueued scripts and styles.
		wp_dequeue_script( 'wc-ai-review-responder' );
		wp_dequeue_style( 'wc-ai-review-responder' );
		// The following is a more robust way to reset between tests.
		global $wp_scripts, $wp_styles;
		$wp_scripts = new \WP_Scripts();
		$wp_styles  = new \WP_Styles();
	}

	/**
	 * Create a simple product using WooCommerce core classes.
	 *
	 * @return int Product ID
	 */
	private function create_wc_simple_product(): int {
		$this->assertTrue( class_exists( '\\WC_Product_Simple' ), 'WooCommerce is not loaded: WC_Product_Simple missing.' );
		$product = new \WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_regular_price( '10' );
		$product->set_price( '10' );
		$product->save();
		return (int) $product->get_id();
	}

	/**
	 * Test that the `add_ai_response_action` method adds the action link for valid product reviews.
	 */
	public function test_add_ai_response_action_adds_action_for_valid_review() {
		// Create a product.
		$product_id = $this->create_wc_simple_product();
		// Create a user with permissions.
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		// Create a review for the product.
		$comment = $this->factory->comment->create_and_get(
			array(
				'comment_post_ID' => $product_id,
				'comment_type'    => 'review',
			)
		);

		$actions = array( 'reply' => 'Reply' );
		$result  = $this->review_actions->add_ai_response_action( $actions, $comment );

		$this->assertArrayHasKey( 'ai_response', $result );
		$this->assertStringContainsString( 'Generate AI Response', $result['ai_response'] );
	}

	/**
	 * Test that the `add_ai_response_action` method does not add the action link for non-product comments.
	 */
	public function test_add_ai_response_action_does_not_add_action_for_non_product_comment() {
		// Create a post.
		$post_id = $this->factory->post->create();
		// Create a user with permissions.
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		// Create a comment for the post.
		$comment = $this->factory->comment->create_and_get(
			array(
				'comment_post_ID' => $post_id,
			)
		);

		$actions = array( 'reply' => 'Reply' );
		$result  = $this->review_actions->add_ai_response_action( $actions, $comment );

		$this->assertArrayNotHasKey( 'ai_response', $result );
	}

	/**
	 * Test that the `add_ai_response_action` method does not add the action link for users without permission.
	 */
	public function test_add_ai_response_action_does_not_add_action_for_insufficient_permissions() {
		// Create a product.
		$product_id = $this->create_wc_simple_product();
		// Create a user without permissions.
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		// Create a review for the product.
		$comment = $this->factory->comment->create_and_get(
			array(
				'comment_post_ID' => $product_id,
				'comment_type'    => 'review',
			)
		);

		$actions = array( 'reply' => 'Reply' );
		$result  = $this->review_actions->add_ai_response_action( $actions, $comment );

		$this->assertArrayNotHasKey( 'ai_response', $result );
	}

	/**
	 * Test that `enqueue_review_scripts` enqueues scripts on the correct page.
	 */
	public function test_enqueue_review_scripts_enqueues_on_correct_page() {
		// Set the current screen to the product reviews page.
		set_current_screen( 'product_page_product-reviews' );

		// Run the enqueue function.
		$this->review_actions->enqueue_review_scripts( 'product_page_product-reviews' );

		// Check if the script and style are enqueued.
		$this->assertTrue( wp_script_is( 'wc-ai-review-responder', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'wc-ai-review-responder', 'enqueued' ) );

		// Check if the script is localized.
		$localized_data = wp_scripts()->get_data( 'wc-ai-review-responder', 'data' );
		$this->assertNotEmpty( $localized_data );
	}

	/**
	 * Test that `enqueue_review_scripts` does not enqueue scripts on other pages.
	 */
	public function test_enqueue_review_scripts_does_not_enqueue_on_other_pages() {
		// Set the current screen to a different page.
		set_current_screen( 'dashboard' );

		// Run the enqueue function.
		$this->review_actions->enqueue_review_scripts( 'dashboard' );

		// Check that the script and style are not enqueued.
		$this->assertFalse( wp_script_is( 'wc-ai-review-responder', 'enqueued' ) );
		$this->assertFalse( wp_style_is( 'wc-ai-review-responder', 'enqueued' ) );
	}
}
