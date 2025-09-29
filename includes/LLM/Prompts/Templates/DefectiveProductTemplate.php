<?php
/**
 * Defective Product prompt template.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Templates;

use WcAiReviewResponder\LLM\Prompts\ReviewContextInterface;

/**
 * Template for responding to reviews about defective products.
 */
class DefectiveProductTemplate implements PromptTemplateInterface {
	/**
	 * Get the template prompt.
	 *
	 * @param ReviewContextInterface $context Review context object.
	 * @return string The formatted prompt.
	 */
	public function get_prompt( ReviewContextInterface $context ): string {
		$prompt  = 'Write a sincere and solution-focused reply to this review about a defective product. ';
		$prompt .= 'Express genuine concern and apologize for the inconvenience caused by the defective item. ';
		$prompt .= 'Acknowledge their frustration and take responsibility for the quality issue. ';
		$prompt .= 'Offer immediate solutions such as replacement, refund, or repair options. ';
		$prompt .= 'Provide clear next steps for resolution and contact information for customer service. ';
		$prompt .= 'Maintain a professional, empathetic tone that prioritizes customer satisfaction (4-5 sentences).';
		$prompt .= "\n\nProduct: {$context->get_product_name()}";
		$prompt .= "\nRating: {$context->get_formatted_rating()}";
		$prompt .= "\nReview: {$context->get_comment()}";
		$prompt .= "\n\nYour response:";

		return $prompt;
	}
}
