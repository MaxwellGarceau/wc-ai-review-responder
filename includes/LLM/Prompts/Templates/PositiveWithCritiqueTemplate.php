<?php
/**
 * Positive Review with Minor Critique prompt template.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Templates;

use WcAiReviewResponder\LLM\Prompts\ReviewContextInterface;

/**
 * Template for responding to positive reviews that include minor critiques.
 */
class PositiveWithCritiqueTemplate implements PromptTemplateInterface {
	/**
	 * Get the template prompt.
	 *
	 * @param ReviewContextInterface $context Review context object.
	 * @return string The formatted prompt.
	 */
	public function get_prompt( ReviewContextInterface $context ): string {
		$prompt  = 'Write a thoughtful reply to this positive review that includes minor constructive feedback. ';
		$prompt .= 'First, express genuine appreciation for their positive comments and high rating. ';
		$prompt .= 'Then, acknowledge their specific concerns or suggestions with empathy and understanding. ';
		$prompt .= 'Offer to address their feedback and explain how their input helps improve the product or service. ';
		$prompt .= 'Maintain a grateful and professional tone while showing that you value their constructive criticism (3-4 sentences).';
		$prompt .= "\n\nProduct: {$context->get_product_name()}";
		$prompt .= "\nRating: {$context->get_formatted_rating()}";
		$prompt .= "\nReview: {$context->get_comment()}";
		$prompt .= "\n\nYour response:";

		return $prompt;
	}
}
