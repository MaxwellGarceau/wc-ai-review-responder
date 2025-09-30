<?php
/**
 * WP-CLI commands for AI Review Responder.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\CLI;

use WcAiReviewResponder\Exceptions\AiResponseFailure;
use WcAiReviewResponder\Exceptions\InvalidReviewException;
use WcAiReviewResponder\Exceptions\RateLimitExceededException;
use WcAiReviewResponder\LLM\Prompts\Moods\MoodsType;
use WcAiReviewResponder\LLM\Prompts\TemplateType;
use WcAiReviewResponder\Models\ReviewModel;
use WcAiReviewResponder\LLM\PromptBuilder;
use WcAiReviewResponder\Clients\GeminiClientFactory;
use WcAiReviewResponder\Validation\ValidateAiResponse;
use WcAiReviewResponder\Validation\ReviewValidator;
use WcAiReviewResponder\Validation\AiInputSanitizer;

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
	 * AI client factory.
	 *
	 * @var \WcAiReviewResponder\Clients\GeminiClientFactory
	 */
	private $ai_client_factory;

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
	 * Input sanitizer dependency.
	 *
	 * @var \WcAiReviewResponder\Validation\AiInputSanitizer
	 */
	private $input_sanitizer;

	/**
	 * Constructor.
	 *
	 * @param ReviewModel         $review_handler     Review handler.
	 * @param PromptBuilder       $prompt_builder     Prompt builder.
	 * @param GeminiClientFactory $ai_client_factory  AI client factory.
	 * @param ValidateAiResponse  $response_validator Response validator.
	 * @param ReviewValidator     $review_validator   Review validator.
	 * @param AiInputSanitizer    $input_sanitizer    Input sanitizer.
	 */
	public function __construct( ReviewModel $review_handler, PromptBuilder $prompt_builder, GeminiClientFactory $ai_client_factory, ValidateAiResponse $response_validator, ReviewValidator $review_validator, AiInputSanitizer $input_sanitizer ) {
		$this->review_handler     = $review_handler;
		$this->prompt_builder     = $prompt_builder;
		$this->ai_client_factory  = $ai_client_factory;
		$this->response_validator = $response_validator;
		$this->review_validator   = $review_validator;
		$this->input_sanitizer    = $input_sanitizer;
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
			\WP_CLI::log( 'Step 1: Prepare Review Data' );
			\WP_CLI::log( '- Fetching review context...' );
			$context = $this->review_handler->get_by_id( $comment_id );
			\WP_CLI::log( '  Review context data: ' . wp_json_encode( $context, JSON_PRETTY_PRINT ) );

			\WP_CLI::log( '- Validating review for AI processing...' );
			$this->review_validator->validate_for_ai_processing( $context );

			\WP_CLI::log( '- Sanitizing input for AI processing...' );
			$clean = $this->input_sanitizer->sanitize( $context );
			\WP_CLI::log( '  Sanitized context data: ' . wp_json_encode( $clean, JSON_PRETTY_PRINT ) );
			\WP_CLI::log( '✓ Review data prepared for all AI operations.' );

			\WP_CLI::log( '' );

			\WP_CLI::log( 'Step 2: Get AI Suggestions' );
			\WP_CLI::log( '- Building suggestion prompt...' );
			$sentiment_prompt_builder = new \WcAiReviewResponder\LLM\Prompts\SentimentAnalysis();
			$suggestion_prompt        = $sentiment_prompt_builder->build_prompt( $clean );
			\WP_CLI::log( '  Generated suggestion prompt: ' . $suggestion_prompt );

			\WP_CLI::log( '- Sending suggestion request to AI...' );
			$suggestion_client   = $this->ai_client_factory->create(
				array(
					'response_mime_type' => 'application/json',
					'response_schema'    => array(
						'type'       => 'object',
						'properties' => array(
							'mood'     => array(
								'type'        => 'string',
								'description' => 'The suggested mood.',
							),
							'template' => array(
								'type'        => 'string',
								'description' => 'The suggested template.',
							),
						),
						'required'   => array( 'mood', 'template' ),
					),
				)
			);
			$suggestion_response = $suggestion_client->get( $suggestion_prompt );

			\WP_CLI::log( '- Validating suggestion response...' );
			\WP_CLI::log( '  Raw AI response: ' . $suggestion_response );
			$suggestions = json_decode( $suggestion_response, true );

			if ( json_last_error() !== JSON_ERROR_NONE || ! isset( $suggestions['mood'] ) || ! isset( $suggestions['template'] ) ) {
				\WP_CLI::log( '  JSON decode error: ' . json_last_error_msg() );
				\WP_CLI::log( '  Decoded suggestions: ' . wp_json_encode( $suggestions, JSON_PRETTY_PRINT ) );
				throw new AiResponseFailure( 'Invalid JSON response from AI for suggestions.' );
			}
			\WP_CLI::log( '✓ AI suggestions received' );
			\WP_CLI::log( '  - Suggested mood: ' . $suggestions['mood'] );
			\WP_CLI::log( '  - Suggested template: ' . $suggestions['template'] );

			\WP_CLI::log( '' );

			\WP_CLI::log( 'Step 3: Generate Final AI Response' );
			\WP_CLI::log( '- Building final prompt with suggestions...' );
			$template = TemplateType::tryFrom( $suggestions['template'] ) ?? TemplateType::DEFAULT;
			$mood     = MoodsType::tryFrom( $suggestions['mood'] ) ?? MoodsType::EMPATHETIC_PROBLEM_SOLVER;

			$this->review_validator->validate_for_ai_processing( $clean );
			$clean = $this->input_sanitizer->sanitize( $clean );

			$prompt = $this->prompt_builder->build_prompt( $clean, $template, $mood );
			\WP_CLI::log( '  Generated final prompt: ' . $prompt );

			\WP_CLI::log( '- Sending final response request to AI...' );
			$response_client = $this->ai_client_factory->create();
			$ai_response     = $response_client->get( $prompt );
			\WP_CLI::log( '  AI response data: ' . wp_json_encode( $ai_response, JSON_PRETTY_PRINT ) );

			\WP_CLI::log( '- Validating final AI response...' );
			$reply = $this->response_validator->validate( $ai_response );
			\WP_CLI::log( '✓ Final response validated.' );
			\WP_CLI::log( '  Validated reply: ' . $reply );

			\WP_CLI::log( '' );

			\WP_CLI::success( 'Generated AI reply: ' . $reply );
		} catch ( InvalidReviewException $e ) {
			\WP_CLI::error( $e->getMessage() );
		} catch ( RateLimitExceededException $e ) {
			\WP_CLI::error( 'Rate limit exceeded: ' . $e->getMessage() );
		} catch ( AiResponseFailure $e ) {
			\WP_CLI::error( $e->getMessage() );
		}
	}
}
