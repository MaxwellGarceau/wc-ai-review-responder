<?php
/**
 * AJAX handler for generating AI responses to product reviews.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder;

use WcAiReviewResponder\Exceptions\Invalid_Arguments_Exception;
use WcAiReviewResponder\Exceptions\Invalid_Review_Exception;
use WcAiReviewResponder\Exceptions\AI_Response_Failure;

/**
 * AJAX handler class for generating AI responses to product reviews.
 */
class Ajax_Handler {
	/**
	 * Boot hooks.
	 */
	public function register() {
		add_action( 'wp_ajax_generate_ai_response', array( $this, 'handle_generate' ) );
	}

	/**
	 * Process the AJAX request.
	 *
	 * @throws Invalid_Arguments_Exception When comment ID is missing or invalid.
	 */
	public function handle_generate() {
		if ( ! current_user_can( 'moderate_comments' ) ) {
			$this->send_error( 'unauthorized', 'Insufficient permissions.' );
		}

		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'generate_ai_response' ) ) {
			$this->send_error( 'invalid_nonce', 'Security check failed.' );
		}

		$comment_id = isset( $_POST['comment_id'] ) ? (int) $_POST['comment_id'] : 0;
		if ( $comment_id <= 0 ) {
			throw new Invalid_Arguments_Exception( 'Missing or invalid comment_id.' );
		}

		try {
			$review_handler = new Review_Handler();
			$context        = $review_handler->get_review_context( $comment_id );

			$api_key   = (string) getenv( 'GEMINI_API_KEY' );
			$ai_client = new AI_Client( $api_key );
			$reply     = $ai_client->generate_reply( $context );

			wp_send_json_success( array( 'reply' => $reply ) );
		} catch ( Invalid_Review_Exception $e ) {
			$this->send_error( 'invalid_review', $e->getMessage() );
		} catch ( AI_Response_Failure $e ) {
			$message = $e->getMessage();
			wp_send_json_error(
				array(
					'code'    => 'ai_failure',
					'message' => $message,
				),
				500
			);
		}
	}

	/**
	 * Send a standardized JSON error and exit.
	 *
	 * @param string $code    Error code.
	 * @param string $message Error message.
	 */
	private function send_error( $code, $message ) {
		wp_send_json_error(
			array(
				'code'    => $code,
				'message' => $message,
			),
			400
		);
	}
}
