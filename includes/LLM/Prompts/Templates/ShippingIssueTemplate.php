<?php
/**
 * Shipping Delay/Issue prompt template.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Templates;

use WcAiReviewResponder\LLM\Prompts\ReviewContextInterface;

/**
 * Template for responding to reviews about shipping delays or issues.
 */
class ShippingIssueTemplate implements PromptTemplateInterface {
	/**
	 * Get the template prompt.
	 *
	 * @param ReviewContextInterface $context Review context object.
	 * @return string The formatted prompt.
	 */
	public function get_prompt( ReviewContextInterface $context ): string {
		$prompt  = 'Write a understanding and proactive reply to this review about shipping delays or issues. ';
		$prompt .= 'Acknowledge the inconvenience caused by the shipping problem and express genuine concern. ';
		$prompt .= 'Explain that shipping delays can occur due to various factors beyond your direct control, but take responsibility for the customer experience. ';
		$prompt .= 'Offer solutions such as expedited shipping for future orders, shipping refunds, or compensation where appropriate. ';
		$prompt .= 'Provide information about how to track orders and contact customer service for shipping inquiries. ';
		$prompt .= 'Maintain a professional tone while showing empathy for their frustration (4-5 sentences).';
		$prompt .= "\n\nProduct: {$context->get_product_name()}";
		$prompt .= "\nRating: {$context->get_formatted_rating()}";
		$prompt .= "\nReview: {$context->get_comment()}";
		$prompt .= "\n\nYour response:";

		return $prompt;
	}
}
