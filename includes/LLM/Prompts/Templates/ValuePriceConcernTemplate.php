<?php
/**
 * Value/Price Concern prompt template.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Templates;

use WcAiReviewResponder\LLM\Prompts\ReviewContextInterface;

/**
 * Template for responding to reviews that question the product's value for its price.
 */
class ValuePriceConcernTemplate implements PromptTemplateInterface {
	/**
	 * Get the template prompt.
	 *
	 * @param ReviewContextInterface $context Review context object.
	 * @return string The formatted prompt.
	 */
	public function get_prompt( ReviewContextInterface $context ): string {
		$prompt  = 'Write a confident and reassuring reply to this review that questions the product\'s value for its price. ';
		$prompt .= 'Acknowledge their concern about pricing and thank them for their honest feedback. ';
		$prompt .= 'Justify the price by highlighting the product\'s superior quality, craftsmanship, and unique features that provide long-term value. ';
		$prompt .= 'Offer to help them get the most value out of their purchase. ';
		$prompt .= 'As a token of appreciation, provide a limited-time loyalty discount for their next purchase. ';
		$prompt .= 'Maintain a professional and appreciative tone, reinforcing the brand\'s commitment to quality and customer value (4-5 sentences).';
		$prompt .= "\n\nProduct: {$context->get_product_name()}";
		$prompt .= "\nRating: {$context->get_formatted_rating()}";
		$prompt .= "\nReview: {$context->get_comment()}";
		$prompt .= "\n\nYour response:";

		return $prompt;
	}
}
