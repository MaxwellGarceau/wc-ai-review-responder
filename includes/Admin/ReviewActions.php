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

use WcAiReviewResponder\LLM\Prompts\TemplateType;

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
		add_filter( 'comment_row_actions', array( $this, 'add_ai_response_action' ), 20, 2 );
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
		// Only add the action for valid product reviews.
		if ( ! $this->is_valid_product_review( $comment ) ) {
			return $actions;
		}

		// Create the AI response generation action.
		$ai_response_action = $this->create_ai_response_action( $comment );

		// Insert the AI response action after the "reply" action.
		return $this->insert_action_after( $actions, 'reply', 'ai_response', $ai_response_action );
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

		// Enqueue the main script.
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

		// Enqueue the CSS file that webpack extracts.
		$css_path = '/build/index.css';
		$css_url  = plugins_url( $css_path, MAIN_PLUGIN_FILE );
		if ( file_exists( dirname( MAIN_PLUGIN_FILE ) . $css_path ) ) {
			wp_enqueue_style(
				'wc-ai-review-responder',
				$css_url,
				array(),
				$script_asset['version']
			);
		}

		// Localize script to provide ajaxurl.
		wp_localize_script(
			'wc-ai-review-responder',
			'wcAiReviewResponder',
			array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'templates' => array_map(
					function ( $enum_case ) {
						return array(
							'value' => $enum_case->value,
							'label' => ucwords( str_replace( '_', ' ', $enum_case->name ) ),
						);
					},
					TemplateType::cases()
				),
			)
		);
	}

	/**
	 * Check if a comment is a valid product review.
	 *
	 * @param WP_Comment|null $comment The comment object.
	 * @return bool True if the comment is a valid product review, false otherwise.
	 * @since 1.0.0
	 */
	private function is_valid_product_review( $comment ): bool {
		// Check if comment exists and is a review.
		if ( ! $comment || 'review' !== get_comment_type( $comment ) ) {
			return false;
		}

		// Check if the comment is associated with a product.
		if ( 'product' !== get_post_type( $comment->comment_post_ID ) ) {
			return false;
		}

		// Check user capabilities.
		if ( ! current_user_can( 'moderate_comments' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Create the AI response generation action HTML.
	 *
	 * @param WP_Comment $comment The comment object.
	 * @return string The HTML for the AI response action link.
	 * @since 1.0.0
	 */
	private function create_ai_response_action( $comment ): string {
		return sprintf(
			'<a href="#" class="ai-generate-response" data-comment-id="%d" data-nonce="%s">%s</a>',
			esc_attr( $comment->comment_ID ),
			esc_attr( wp_create_nonce( 'generate_ai_response' ) ),
			esc_html__( 'Generate AI Response', 'wc_ai_review_responder' )
		);
	}

	/**
	 * Insert a new action after a specified action in the actions array.
	 *
	 * @param array  $actions        The original actions array.
	 * @param string $after_key      The key to insert after.
	 * @param string $new_key        The key for the new action.
	 * @param string $new_action     The HTML for the new action.
	 * @return array The reordered actions array.
	 * @since 1.0.0
	 */
	private function insert_action_after( array $actions, string $after_key, string $new_key, string $new_action ): array {
		$reordered_actions = array();

		foreach ( $actions as $key => $action ) {
			$reordered_actions[ $key ] = $action;

			// Insert new action after the specified key.
			if ( $after_key === $key ) {
				$reordered_actions[ $new_key ] = $new_action;
			}
		}

		// If the target action wasn't found, just append the new action.
		if ( ! isset( $actions[ $after_key ] ) ) {
			$reordered_actions[ $new_key ] = $new_action;
		}

		return $reordered_actions;
	}
}
