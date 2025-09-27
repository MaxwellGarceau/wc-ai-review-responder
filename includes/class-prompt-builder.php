<?php
/**
 * Prompt builder implementation.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder;

class Prompt_Builder implements Build_Prompt_Interface {
	/**
	 * {@inheritDoc}
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
}
