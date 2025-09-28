<?php
/**
 * Admin review actions functionality for WC AI Review Responder.
 *
 * Adds the "Generate AI Response" link to the comment row actions on
 * /wp-admin/edit.php?post_type=product&page=product-reviews
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Admin;

/**
 * Admin review actions class for adding AI response generation links.
 */
class ReviewActions {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'comment_row_actions', array( $this, 'add_ai_response_action' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_review_scripts' ) );
	}

	/**
	 * Add AI response generation action to comment row actions.
	 *
	 * @param array      $actions Array of action links.
	 * @param WP_Comment $comment The comment object.
	 * @return array Modified actions array.
	 * @since 1.0.0
	 */
	public function add_ai_response_action( array $actions, $comment ): array {
		// Only add the action for product reviews.
		if ( ! $comment || 'review' !== get_comment_type( $comment ) ) {
			return $actions;
		}

		// Check if the comment is associated with a product.
		if ( 'product' !== get_post_type( $comment->comment_post_ID ) ) {
			return $actions;
		}

		// Check user capabilities.
		if ( ! current_user_can( 'moderate_comments' ) ) {
			return $actions;
		}

		// Add the AI response generation action.
		$actions['ai_response'] = sprintf(
			'<a href="#" class="ai-generate-response" data-comment-id="%d" data-nonce="%s">%s</a>',
			esc_attr( $comment->comment_ID ),
			esc_attr( wp_create_nonce( 'generate_ai_response' ) ),
			esc_html__( 'Generate AI Response', 'wc_ai_review_responder' )
		);

		return $actions;
	}

	/**
	 * Enqueue scripts for the reviews page.
	 *
	 * @param string $hook_suffix The current admin page.
	 * @since 1.0.0
	 */
	public function enqueue_review_scripts( string $hook_suffix ): void {
		// Only enqueue on the product reviews page.
		if ( 'product_page_product-reviews' !== $hook_suffix ) {
			return;
		}

		// Enqueue the main script (which now includes admin review actions).
		$script_path       = '/build/index.js';
		$script_asset_path = dirname( MAIN_PLUGIN_FILE ) . '/build/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => filemtime( dirname( MAIN_PLUGIN_FILE ) . $script_path ),
			);
		$script_url        = plugins_url( $script_path, MAIN_PLUGIN_FILE );

		wp_enqueue_script(
			'wc-ai-review-responder',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		// Localize script to provide ajaxurl.
		wp_localize_script(
			'wc-ai-review-responder',
			'wcAiReviewResponder',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}
}
