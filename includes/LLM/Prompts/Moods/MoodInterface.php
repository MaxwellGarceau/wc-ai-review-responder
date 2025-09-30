<?php
/**
 * Mood interface for prompt generation.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Moods;

use WcAiReviewResponder\LLM\Prompts\ReviewContextInterface;

/**
 * Interface for defining different moods in prompt generation.
 */
interface MoodInterface {


	/**
	 * Apply mood to a prompt.
	 *
	 * @param string                 $prompt The base prompt.
	 * @param ReviewContextInterface $context Review context object.
	 * @return string The prompt with mood applied.
	 */
	public function apply( string $prompt, ReviewContextInterface $context ): string;
}
