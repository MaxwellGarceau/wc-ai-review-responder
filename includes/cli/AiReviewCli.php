<?php
/**
 * WP-CLI commands for AI Review Responder.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\CLI;

use WcAiReviewResponder\Models\ReviewModel;
use WcAiReviewResponder\LLM\PromptBuilder;
use WcAiReviewResponder\Clients\GeminiClient;
use WcAiReviewResponder\Validation\ValidateAiResponse;
use WcAiReviewResponder\Validation\ReviewValidator;
use WcAiReviewResponder\Exceptions\InvalidReviewException;
use WcAiReviewResponder\Exceptions\AiResponseFailure;

/**
 * WP-CLI command class to exercise the integration flow for generating replies.
 */
class AiReviewCli {
	/**
	 * Review handler dependency.
	 *
	 * @var \WcAiReviewResponder\Models\ReviewModel
	 */
	private $review_handler;

	/**
	 * Prompt builder dependency.
	 *
	 * @var \WcAiReviewResponder\LLM\PromptBuilder
	 */
	private $prompt_builder;

	/**
	 * AI client dependency.
	 *
	 * @var \WcAiReviewResponder\Clients\GeminiClient
	 */
	private $ai_client;

	/**
	 * Response validator dependency.
	 *
	 * @var \WcAiReviewResponder\Validation\ValidateAiResponse
	 */
	private $response_validator;

	/**
	 * Review validator dependency.
	 *
	 * @var \WcAiReviewResponder\Validation\ReviewValidator
	 */
	private $review_validator;

	/**
	 * Constructor.
	 *
	 * @param ReviewModel        $review_handler     Review handler.
	 * @param PromptBuilder      $prompt_builder     Prompt builder.
	 * @param GeminiClient       $ai_client          AI client.
	 * @param ValidateAiResponse $response_validator Response validator.
	 * @param ReviewValidator    $review_validator   Review validator.
	 */
	public function __construct( ReviewModel $review_handler, PromptBuilder $prompt_builder, GeminiClient $ai_client, ValidateAiResponse $response_validator, ReviewValidator $review_validator ) {
		$this->review_handler     = $review_handler;
		$this->prompt_builder     = $prompt_builder;
		$this->ai_client          = $ai_client;
		$this->response_validator = $response_validator;
		$this->review_validator   = $review_validator;
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
			\WP_CLI::log( 'Step 1: Fetching review context...' );
			$context = $this->review_handler->get_by_id( $comment_id );
			\WP_CLI::log( '✓ This is the output from Fetching review context' );
			\WP_CLI::log( 'Review context data: ' . wp_json_encode( $context, JSON_PRETTY_PRINT ) );

			\WP_CLI::log( '' );

			\WP_CLI::log( 'Step 1.5: Validating review for AI processing...' );
			$this->review_validator->validate_for_ai_processing( $context );
			\WP_CLI::log( '✓ Review validation passed' );

			\WP_CLI::log( '' );

			\WP_CLI::log( 'Step 2: Building AI prompt...' );
			$prompt = $this->prompt_builder->build_prompt( $context );
			\WP_CLI::log( '✓ This is the output from Building AI prompt' );
			\WP_CLI::log( 'Generated prompt: ' . $prompt );

			\WP_CLI::log( '' );

			\WP_CLI::log( 'Step 3: Sending request to AI client...' );
			$ai_response = $this->ai_client->get( $prompt );
			\WP_CLI::log( '✓ This is the output from Sending request to AI client' );
			\WP_CLI::log( 'AI response data: ' . wp_json_encode( $ai_response, JSON_PRETTY_PRINT ) );

			\WP_CLI::log( '' );

			\WP_CLI::log( 'Step 4: Validating AI response...' );
			$reply = $this->response_validator->validate( $ai_response );
			\WP_CLI::log( '✓ This is the output from Validating AI response' );
			\WP_CLI::log( 'Validated reply: ' . $reply );

			\WP_CLI::log( '' );

			\WP_CLI::success( 'Generated AI reply: ' . $reply );
		} catch ( InvalidReviewException $e ) {
			\WP_CLI::error( $e->getMessage() );
		} catch ( AiResponseFailure $e ) {
			\WP_CLI::error( $e->getMessage() );
		}
	}
}
