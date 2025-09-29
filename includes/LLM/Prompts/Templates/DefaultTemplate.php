<?php
/**
 * Default prompt template - Brand safe for variety of situations.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Templates;

use WcAiReviewResponder\LLM\Prompts\ReviewContextInterface;

/**
 * Default template for general review responses.
 */
class DefaultTemplate implements PromptTemplateInterface {
	/**
	 * Get the template prompt.
	 *
	 * @param ReviewContextInterface $context Review context object.
	 * @return string The formatted prompt.
	 */
	public function get_prompt( ReviewContextInterface $context ): string {
		$prompt  = 'Write a professional, friendly, and brand-safe reply to this product review. ';
		$prompt .= 'Keep the response concise (2-3 sentences), maintain a positive tone, and address the customer\'s feedback appropriately. ';
		$prompt .= 'If the review mentions specific issues, acknowledge them and offer helpful solutions. ';
		$prompt .= 'Always thank the customer for their feedback.';
		$prompt .= "\n\nProduct: {$context->get_product_name()}";
		$prompt .= "\nRating: {$context->get_formatted_rating()}";
		$prompt .= "\nReview: {$context->get_comment()}";
		$prompt .= "\n\nYour response:";

		return $prompt;
	}
}
