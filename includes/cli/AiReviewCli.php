<?php
/**
 * WP-CLI commands for AI Review Responder.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\CLI;

use WcAiReviewResponder\Models\Review_Model;
use WcAiReviewResponder\LLM\Build_Prompt_Interface;
use WcAiReviewResponder\Clients\AI_Client;
use WcAiReviewResponder\Validation\Validate_AI_Response_Interface;
use WcAiReviewResponder\Exceptions\Invalid_Review_Exception;
use WcAiReviewResponder\Exceptions\AI_Response_Failure;

/**
 * WP-CLI command class to exercise the integration flow for generating replies.
 */
class AiReviewCli {
	/**
	 * Review handler dependency.
	 *
	 * @var \WcAiReviewResponder\Models\Review_Model
	 */
	private $review_handler;

	/**
	 * Prompt builder dependency.
	 *
	 * @var \WcAiReviewResponder\LLM\Build_Prompt_Interface
	 */
	private $prompt_builder;

	/**
	 * AI client dependency.
	 *
	 * @var \WcAiReviewResponder\Clients\AI_Client
	 */
	private $ai_client;

	/**
	 * Response validator dependency.
	 *
	 * @var \WcAiReviewResponder\Validation\Validate_AI_Response_Interface
	 */
	private $response_validator;

	/**
	 * Constructor.
	 *
	 * @param Review_Model                   $review_handler     Review handler.
	 * @param Build_Prompt_Interface         $prompt_builder     Prompt builder.
	 * @param AI_Client                      $ai_client          AI client.
	 * @param Validate_AI_Response_Interface $response_validator Response validator.
	 */
	public function __construct( Review_Model $review_handler, Build_Prompt_Interface $prompt_builder, AI_Client $ai_client, Validate_AI_Response_Interface $response_validator ) {
		$this->review_handler     = $review_handler;
		$this->prompt_builder     = $prompt_builder;
		$this->ai_client          = $ai_client;
		$this->response_validator = $response_validator;
	}

	/**
	 * Test generating an AI reply for a review comment.
	 *
	 * ## OPTIONS
	 *
	 * <comment_id>
	 * : The ID of the review comment to process.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ai-review test 123
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Associative args.
	 */
	public function test( $args, $assoc_args ) {
		list( $comment_id ) = $args;
		$comment_id         = (int) $comment_id;

		// Parameter is part of the WP-CLI signature but unused here.
		unset( $assoc_args );

		if ( $comment_id <= 0 ) {
			\WP_CLI::error( 'Missing or invalid comment_id.' );
		}

		try {
			$context     = $this->review_handler->get_by_id( $comment_id );
			$prompt      = $this->prompt_builder->build_prompt( $context );
			$ai_response = $this->ai_client->request_reply( $prompt );
			$reply       = $this->response_validator->validate( $ai_response );

			\WP_CLI::success( $reply );
		} catch ( Invalid_Review_Exception $e ) {
			\WP_CLI::error( $e->getMessage() );
		} catch ( AI_Response_Failure $e ) {
			\WP_CLI::error( $e->getMessage() );
		}
	}
}
