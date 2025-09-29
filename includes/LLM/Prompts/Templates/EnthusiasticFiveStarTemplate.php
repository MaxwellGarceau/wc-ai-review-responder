<?php
/**
 * Enthusiastic 5-Star Review prompt template.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Templates;

use WcAiReviewResponder\LLM\Prompts\ReviewContextInterface;

/**
 * Template for responding to enthusiastic 5-star reviews.
 */
class EnthusiasticFiveStarTemplate implements PromptTemplateInterface {
	/**
	 * Get the template prompt.
	 *
	 * @param ReviewContextInterface $context Review context object.
	 * @return string The formatted prompt.
	 */
	public function get_prompt( ReviewContextInterface $context ): string {
		$prompt  = 'Write an enthusiastic and grateful reply to this 5-star review. ';
		$prompt .= 'Express genuine appreciation for the customer\'s positive feedback and their specific praise. ';
		$prompt .= 'Match their enthusiasm while maintaining professionalism. ';
		$prompt .= 'Encourage them to share their experience with others and invite them to explore more products. ';
		$prompt .= 'Keep the response warm, personal, and celebratory (2-3 sentences).';
		$prompt .= "\n\nProduct: {$context->get_product_name()}";
		$prompt .= "\nRating: {$context->get_formatted_rating()}";
		$prompt .= "\nReview: {$context->get_comment()}";
		$prompt .= "\n\nYour response:";

		return $prompt;
	}
}
