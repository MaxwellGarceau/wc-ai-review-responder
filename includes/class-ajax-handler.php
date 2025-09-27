<?php

namespace WcAiReviewResponder;

use WcAiReviewResponder\Exceptions\Invalid_Arguments_Exception;
use WcAiReviewResponder\Exceptions\Invalid_Review_Exception;
use WcAiReviewResponder\Exceptions\AI_Response_Failure;

/**
 * Handles AJAX requests for generating AI responses.
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
	 */
	public function handle_generate() {
		if ( ! current_user_can( 'moderate_comments' ) ) {
			$this->send_error( 'unauthorized', 'Insufficient permissions.' );
		}

		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'generate_ai_response' ) ) {
			$this->send_error( 'invalid_nonce', 'Security check failed.' );
		}

		$commentId = isset( $_POST['comment_id'] ) ? (int) $_POST['comment_id'] : 0;
		if ( $commentId <= 0 ) {
			throw new Invalid_Arguments_Exception( 'Missing or invalid comment_id.' );
		}

		try {
			$reviewHandler = new Review_Handler();
			$context       = $reviewHandler->get_review_context( $commentId );

			$apiKey   = (string) getenv( 'GEMINI_API_KEY' );
			$aiClient = new AI_Client( $apiKey );
			$reply    = $aiClient->generate_reply( $context );

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
	 * @param string $code
	 * @param string $message
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
