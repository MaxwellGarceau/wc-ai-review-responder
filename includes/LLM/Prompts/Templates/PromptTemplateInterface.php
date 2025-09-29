<?php
/**
 * Interface for AI prompt templates.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Templates;

use WcAiReviewResponder\LLM\Prompts\ReviewContextInterface;

/**
 * Interface that all prompt templates must implement.
 */
interface PromptTemplateInterface {
	/**
	 * Get the formatted prompt for the given review context.
	 *
	 * @param ReviewContextInterface $context Review context object.
	 * @return string The formatted prompt to send to the AI provider.
	 */
	public function get_prompt( ReviewContextInterface $context ): string;
}
