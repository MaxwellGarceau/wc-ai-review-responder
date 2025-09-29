<?php
/**
 * Product Misunderstanding prompt template.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Templates;

use WcAiReviewResponder\LLM\Prompts\ReviewContextInterface;

/**
 * Template for responding to reviews that show product misunderstanding.
 */
class ProductMisunderstandingTemplate implements PromptTemplateInterface {
	/**
	 * Get the template prompt.
	 *
	 * @param ReviewContextInterface $context Review context object.
	 * @return string The formatted prompt.
	 */
	public function get_prompt( ReviewContextInterface $context ): string {
		$prompt  = 'Write a helpful and educational reply to this review that appears to misunderstand the product. ';
		$prompt .= 'Politely clarify any misconceptions about the product\'s features, usage, or intended purpose. ';
		$prompt .= 'Provide helpful information that addresses their concerns while being respectful of their experience. ';
		$prompt .= 'Offer additional resources or support if they need help understanding how to use the product effectively. ';
		$prompt .= 'Maintain a patient and understanding tone, acknowledging that product information can sometimes be unclear (3-4 sentences).';
		$prompt .= "\n\nProduct: {$context->get_product_name()}";
		$prompt .= "\nRating: {$context->get_formatted_rating()}";
		$prompt .= "\nReview: {$context->get_comment()}";
		$prompt .= "\n\nYour response:";

		return $prompt;
	}
}
