<?php
/**
 * Prompt builder implementation.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM;

/**
 * Builds prompts from a well-defined review context.
 */
class PromptBuilder implements BuildPromptInterface {
	/**
	 * Build a prompt string from the provided context.
	 *
	 * @param array{rating:int,comment:string,product_name:string} $context Review context shape.
	 * @return string Prompt to send to the AI provider.
	 */
	public function build_prompt( array $context ): string {
		$rating  = isset( $context['rating'] ) ? (int) $context['rating'] : 0;
		$comment = isset( $context['comment'] ) ? (string) $context['comment'] : '';
		$product = isset( $context['product_name'] ) ? (string) $context['product_name'] : '';

		$prompt  = 'Write a short, friendly, brand-safe reply to this product review.';
		$prompt .= "\nProduct: {$product}";
		$prompt .= "\nRating: {$rating}/5";
		$prompt .= "\nReview: {$comment}";
		$prompt .= "\nYour reply:";

		return $prompt;
	}
}
