<?php
/**
 * AJAX handler for generating AI responses to product reviews.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Endpoints;

use WcAiReviewResponder\Exceptions\InvalidArgumentsException;
use WcAiReviewResponder\Exceptions\InvalidReviewException;
use WcAiReviewResponder\Exceptions\AiResponseFailure;
use WcAiReviewResponder\Enums\ErrorType;
use WcAiReviewResponder\Enums\HttpStatus;

/**
 * AJAX handler class for generating AI responses to product reviews.
 */
class AjaxHandler {
	/**
	 * Review handler dependency.
	 *
	 * @var \WcAiReviewResponder\Models\ModelInterface
	 */
	private $review_handler;

	/**
	 * Prompt builder dependency.
	 *
	 * @var \WcAiReviewResponder\LLM\BuildPromptInterface
	 */
	private $prompt_builder;

	/**
	 * AI client dependency.
	 *
	 * @var \WcAiReviewResponder\Clients\AiClientInterface
	 */
	private $ai_client;

	/**
	 * Response validator dependency.
	 *
	 * @var \WcAiReviewResponder\Validation\ValidateAiResponseInterface
	 */
	private $response_validator;

	/**
	 * Input validator dependency.
	 *
	 * @var \WcAiReviewResponder\Validation\ValidateAiInput
	 */
	private $input_validator;

	/**
	 * Constructor.
	 *
	 * Initializes dependencies used during the AJAX request lifecycle.
	 *
	 * @param \WcAiReviewResponder\Models\ModelInterface                  $review_handler     Review handler.
	 * @param \WcAiReviewResponder\LLM\BuildPromptInterface               $prompt_builder     Prompt builder.
	 * @param \WcAiReviewResponder\Clients\AiClientInterface              $ai_client          AI client.
	 * @param \WcAiReviewResponder\Validation\ValidateAiResponseInterface $response_validator Response validator.
	 * @param \WcAiReviewResponder\Validation\ValidateAiInput             $input_validator    Input validator.
	 */
	public function __construct( \WcAiReviewResponder\Models\ModelInterface $review_handler, \WcAiReviewResponder\LLM\BuildPromptInterface $prompt_builder, \WcAiReviewResponder\Clients\AiClientInterface $ai_client, \WcAiReviewResponder\Validation\ValidateAiResponseInterface $response_validator, \WcAiReviewResponder\Validation\ValidateAiInput $input_validator ) {
		$this->review_handler     = $review_handler;
		$this->prompt_builder     = $prompt_builder;
		$this->ai_client          = $ai_client;
		$this->response_validator = $response_validator;
		$this->input_validator    = $input_validator;
	}
	/**
	 * Boot hooks.
	 */
	public function register() {
		add_action( 'wp_ajax_generate_ai_response', array( $this, 'handle_generate' ) );
	}

	/**
	 * Process the AJAX request.
	 *
	 * Response examples:
	 *
	 * Success:
	 * {
	 *   "success": true,
	 *   "data": {
	 *     "reply": "Thank you for your review! We're glad you're enjoying the Amazing Widget. We appreciate your feedback about pricing and are always working to provide the best value for our customers."
	 *   }
	 * }
	 *
	 * Error:
	 * {
	 *   "success": false,
	 *   "data": {
	 *     "error_type": "invalid_review",
	 *     "message": "Review is missing required data."
	 *   }
	 * }
	 *
	 * @return void
	 * @throws InvalidArgumentsException When the request contains an invalid comment ID.
	 */
	public function handle_generate(): void {
		if ( ! current_user_can( 'moderate_comments' ) ) {
			$this->send_error( ErrorType::UNAUTHORIZED, 'Insufficient permissions.', HttpStatus::UNAUTHORIZED );
		}

		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'generate_ai_response' ) ) {
			$this->send_error( ErrorType::INVALID_NONCE, 'Security check failed.', HttpStatus::FORBIDDEN );
		}

		$comment_id = isset( $_POST['comment_id'] ) ? (int) $_POST['comment_id'] : 0;
		if ( $comment_id <= 0 ) {
			throw new InvalidArgumentsException( 'Missing or invalid comment_id.' );
		}

		try {
			$context     = $this->review_handler->get_by_id( $comment_id );
			$clean       = $this->input_validator->validate( $context );
			$prompt      = $this->prompt_builder->build_prompt( $clean );
			$ai_response = $this->ai_client->get( $prompt );
			$reply       = $this->response_validator->validate( $ai_response );

			wp_send_json_success( array( 'reply' => $reply ) );
		} catch ( InvalidReviewException $e ) {
			$this->send_error( ErrorType::INVALID_REVIEW, $e->getMessage(), HttpStatus::BAD_REQUEST );
		} catch ( AiResponseFailure $e ) {
			$this->send_error( ErrorType::AI_FAILURE, $e->getMessage(), HttpStatus::INTERNAL_SERVER_ERROR );
		}
	}

	/**
	 * Send a standardized JSON error and exit.
	 *
	 * @param ErrorType  $error_type Error type.
	 * @param string     $message    Error message.
	 * @param HttpStatus $code       HTTP status code.
	 */
	private function send_error( ErrorType $error_type, string $message, HttpStatus $code ): void {
		wp_send_json_error(
			array(
				'error_type' => $error_type->value,
				'message'    => $message,
			),
			$code->value
		);
	}
}
