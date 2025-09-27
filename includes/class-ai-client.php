<?php
/**
 * AI client for interacting with the Gemini API.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder;

use WcAiReviewResponder\Exceptions\AI_Response_Failure;

/**
 * AI client class that sends prompts to the Gemini API and returns raw responses.
 */
class AI_Client {
	/**
	 * Gemini API key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Prompt builder dependency.
	 *
	 * @var \WcAiReviewResponder\LLM\Build_Prompt_Interface
	 */
	private $prompt_builder;

	/**
	 * Constructor.
	 *
	 * @param string                                          $api_key        Gemini API key.
	 * @param \WcAiReviewResponder\LLM\Build_Prompt_Interface $prompt_builder Prompt builder implementation.
	 */
	public function __construct( string $api_key, \WcAiReviewResponder\LLM\Build_Prompt_Interface $prompt_builder ) {
		$this->api_key        = $api_key;
		$this->prompt_builder = $prompt_builder;
	}

	// build_prompt moved to Prompt_Builder via dependency injection.

	/**
	 * Request a reply from the AI provider using a prepared prompt.
	 *
	 * Note: This is a scaffold. Actual SDK integration will be implemented later.
	 *
	 * @param string $prompt Prepared prompt string.
	 * @return string Raw AI response.
	 * @throws AI_Response_Failure When API key is missing or AI returns empty response.
	 */
	public function request_reply( string $prompt ): string {
		if ( empty( $this->api_key ) ) {
			throw new AI_Response_Failure( 'Missing Gemini API key.' );
		}

		// Placeholder: integration with Gemini SDK goes here.
		// For MVP scaffolding, return a deterministic stub for visibility.
		$reply = 'Thank you so much for your review! We appreciate your feedback.';

		if ( ! is_string( $reply ) || '' === trim( $reply ) ) {
			throw new AI_Response_Failure( 'AI returned an empty response.', 0, null, array( 'prompt' => wp_kses_post( $prompt ) ) );
		}

		return $reply;
	}
}
