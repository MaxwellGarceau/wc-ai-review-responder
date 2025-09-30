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
use WcAiReviewResponder\Localization\Translations;

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
	 * @throws AiResponseFailure When invalid comment ID is provided.
	 */
	public function test( $args, $assoc_args ) {
		list( $comment_id ) = $args;
		$comment_id         = (int) $comment_id;
		$cli_strings        = Translations::get_cli_strings();

		// Parameter is part of the WP-CLI signature but unused here.
		unset( $assoc_args );

		if ( $comment_id <= 0 ) {
			\WP_CLI::error( $cli_strings['missingCommentId'] );
		}

		try {
			\WP_CLI::log( $cli_strings['step1PrepareData'] );
			\WP_CLI::log( $cli_strings['fetchingReviewContext'] );
			$context = $this->review_handler->get_by_id( $comment_id );
			\WP_CLI::log( $cli_strings['reviewContextData'] . wp_json_encode( $context, JSON_PRETTY_PRINT ) );

			\WP_CLI::log( $cli_strings['validatingReview'] );
			$this->review_validator->validate_for_ai_processing( $context );

			\WP_CLI::log( $cli_strings['sanitizingInput'] );
			$clean = $this->input_sanitizer->sanitize( $context );
			\WP_CLI::log( $cli_strings['sanitizedContextData'] . wp_json_encode( $clean, JSON_PRETTY_PRINT ) );
			\WP_CLI::log( $cli_strings['reviewDataPrepared'] );

			\WP_CLI::log( '' );

			\WP_CLI::log( $cli_strings['step2GetSuggestions'] );
			\WP_CLI::log( $cli_strings['buildingSuggestionPrompt'] );
			$sentiment_prompt_builder = new \WcAiReviewResponder\LLM\Prompts\SentimentAnalysis();
			$suggestion_prompt        = $sentiment_prompt_builder->build_prompt( $clean );
			\WP_CLI::log( $cli_strings['generatedSuggestionPrompt'] . $suggestion_prompt );

			\WP_CLI::log( $cli_strings['sendingSuggestionRequest'] );
			$suggestion_client   = $this->ai_client_factory->create(
				array(
					'response_mime_type' => 'application/json',
					'response_schema'    => array(
						'type'       => 'object',
						'properties' => array(
							'mood'     => array(
								'type'        => 'string',
								'description' => $cli_strings['suggestedMood'],
							),
							'template' => array(
								'type'        => 'string',
								'description' => $cli_strings['suggestedTemplate'],
							),
						),
						'required'   => array( 'mood', 'template' ),
					),
				)
			);
			$suggestion_response = $suggestion_client->get( $suggestion_prompt );

			\WP_CLI::log( $cli_strings['validatingSuggestionResponse'] );
			\WP_CLI::log( $cli_strings['rawAiResponse'] . $suggestion_response );
			$suggestions = json_decode( $suggestion_response, true );

			if ( json_last_error() !== JSON_ERROR_NONE || ! isset( $suggestions['mood'] ) || ! isset( $suggestions['template'] ) ) {
				\WP_CLI::log( $cli_strings['jsonDecodeError'] . json_last_error_msg() );
				\WP_CLI::log( $cli_strings['decodedSuggestions'] . wp_json_encode( $suggestions, JSON_PRETTY_PRINT ) );
				throw new AiResponseFailure( $cli_strings['invalidJsonResponse'] );
			}
			\WP_CLI::log( $cli_strings['aiSuggestionsReceived'] );
			\WP_CLI::log( $cli_strings['suggestedMoodValue'] . $suggestions['mood'] );
			\WP_CLI::log( $cli_strings['suggestedTemplateValue'] . $suggestions['template'] );

			\WP_CLI::log( '' );

			\WP_CLI::log( $cli_strings['step3GenerateResponse'] );
			\WP_CLI::log( $cli_strings['buildingFinalPrompt'] );
			$template = TemplateType::tryFrom( $suggestions['template'] ) ?? TemplateType::DEFAULT;
			$mood     = MoodsType::tryFrom( $suggestions['mood'] ) ?? MoodsType::EMPATHETIC_PROBLEM_SOLVER;

			$this->review_validator->validate_for_ai_processing( $clean );
			$clean = $this->input_sanitizer->sanitize( $clean );

			$prompt = $this->prompt_builder->build_prompt( $clean, $template, $mood );
			\WP_CLI::log( $cli_strings['generatedFinalPrompt'] . $prompt );

			\WP_CLI::log( $cli_strings['sendingFinalRequest'] );
			$response_client = $this->ai_client_factory->create();
			$ai_response     = $response_client->get( $prompt );
			\WP_CLI::log( $cli_strings['aiResponseData'] . wp_json_encode( $ai_response, JSON_PRETTY_PRINT ) );

			\WP_CLI::log( $cli_strings['validatingFinalResponse'] );
			$reply = $this->response_validator->validate( $ai_response );
			\WP_CLI::log( $cli_strings['finalResponseValidated'] );
			\WP_CLI::log( $cli_strings['validatedReply'] . $reply );

			\WP_CLI::log( '' );

			\WP_CLI::success( $cli_strings['generatedAiReply'] . $reply );
		} catch ( InvalidReviewException $e ) {
			\WP_CLI::error( $e->getMessage() );
		} catch ( RateLimitExceededException $e ) {
			\WP_CLI::error( $cli_strings['rateLimitExceeded'] . $e->getMessage() );
		} catch ( AiResponseFailure $e ) {
			\WP_CLI::error( $e->getMessage() );
		}
	}
}
