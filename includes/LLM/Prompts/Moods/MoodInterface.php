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
	 * Get the mood name.
	 *
	 * @return string The mood name.
	 */
	public function get_name(): string;

	/**
	 * Get the mood description.
	 *
	 * @return string The mood description.
	 */
	public function get_description(): string;

	/**
	 * Apply mood to a prompt.
	 *
	 * @param string                 $prompt The base prompt.
	 * @param ReviewContextInterface $context Review context object.
	 * @return string The prompt with mood applied.
	 */
	public function apply_mood( string $prompt, ReviewContextInterface $context ): string;
}
