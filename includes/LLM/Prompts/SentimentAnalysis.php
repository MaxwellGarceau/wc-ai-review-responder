<?php
/**
 * Prompt for AI-powered sentiment analysis of reviews.
 *
 * @package WcAiReviewResponder
 * @since 1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts;

use WcAiReviewResponder\LLM\BuildPromptInterface;
use WcAiReviewResponder\LLM\Prompts\Moods\MoodsType;

/**
 * Builds a prompt to ask the AI for sentiment analysis of a review.
 */
class SentimentAnalysis implements BuildPromptInterface {

	/**
	 * Build a prompt string from the provided context.
	 *
	 * This prompt asks the AI to analyze the review and suggest a mood and template.
	 *
	 * @param array{rating:int,comment:string,product_name:string} $context Review context shape.
	 * @param TemplateType                                         $template Template type (ignored).
	 * @param MoodsType                                            $mood Mood type (ignored).
	 *
	 * @return string Prompt to send to the AI provider.
	 */
	public function build_prompt( array $context, TemplateType $template = TemplateType::DEFAULT, MoodsType $mood = MoodsType::EMPATHETIC_PROBLEM_SOLVER ): string {
		$review_context = new ReviewContext( $context );
		$rating         = $review_context->get_rating();
		$product_name   = $review_context->get_product_name();
		$comment        = $review_context->get_comment();

		$available_moods     = implode( ', ', array_column( MoodsType::cases(), 'value' ) );
		$available_templates = implode( ', ', array_column( TemplateType::cases(), 'value' ) );

		return "
You are a WooCommerce support agent. Your task is to analyze the sentiment of a customer review and suggest the best tone and response template for replying.

The available moods are: '{$available_moods}'.
The available templates are: '{$available_templates}'.

Based on the following review, please suggest the most appropriate mood and template.

Review:
Rating: {$rating}/5 stars
Product: {$product_name}
Comment: {$comment}

Your response must be in JSON format, like this:
{\"mood\": \"suggested_mood\", \"template\": \"suggested_template\"}

For example:
{\"mood\": \"empathetic_problem_solver\", \"template\": \"defective_product\"}

Only return the JSON object. Do not include any other text or explanation.
";
	}
}
