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
 * AI client class for interacting with the Gemini API.
 */
class AI_Client {
	/**
	 * Gemini API key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Constructor.
	 *
	 * @param string $api_key Gemini API key.
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Build a prompt using review and product context.
	 *
	 * @param array<string,mixed> $context Review context data.
	 * @return string Generated prompt.
	 */
	public function build_prompt( $context ) {
		$rating  = isset( $context['rating'] ) ? (int) $context['rating'] : 0;
		$comment = isset( $context['comment'] ) ? (string) $context['comment'] : '';
		$product = isset( $context['product_name'] ) ? (string) $context['product_name'] : '';

		$prompt  = 'Write a short, friendly, brand-safe reply to this product review.';
		$prompt .= "\nProduct: {$product}";
		$prompt .= "\nRating: {$rating}/5";
		$prompt .= "\nReview: {$comment}";
		$prompt .= "\nReply:";

		return $prompt;
	}

	/**
	 * Generate a reply using the AI provider.
	 *
	 * Note: This is a scaffold. Actual SDK integration will be implemented later.
	 *
	 * @param array<string,mixed> $context Review context data.
	 * @return string Generated AI reply.
	 * @throws AI_Response_Failure When API key is missing or AI returns empty response.
	 */
	public function generate_reply( $context ) {
		if ( empty( $this->api_key ) ) {
			throw new AI_Response_Failure( 'Missing Gemini API key.' );
		}

		$prompt = $this->build_prompt( $context );

		// Placeholder: integration with Gemini SDK goes here.
		// For MVP scaffolding, return a deterministic stub for visibility.
		$reply = 'Thank you so much for your review! We appreciate your feedback.';

		if ( ! is_string( $reply ) || '' === trim( $reply ) ) {
			throw new AI_Response_Failure( 'AI returned an empty response.', 0, null, array( 'prompt' => wp_kses_post( $prompt ) ) );
		}

		return wp_kses_post( $reply );
	}
}
