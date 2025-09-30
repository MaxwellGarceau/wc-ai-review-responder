<?php
/**
 * Enthusiastic Appreciator mood for positive reviews.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Moods;

use WcAiReviewResponder\LLM\Prompts\ReviewContextInterface;

/**
 * Mood for handling positive reviews with enthusiasm and appreciation.
 */
class EnthusiasticAppreciator implements MoodInterface {


	/**
	 * Apply mood to a prompt.
	 *
	 * @param string                 $prompt The base prompt.
	 * @param ReviewContextInterface $context Review context object.
	 * @return string The prompt with mood applied.
	 */
	public function apply( string $prompt, ReviewContextInterface $context ): string {
		$mood_prefix  = 'Write with genuine enthusiasm and gratitude. Express sincere appreciation for their positive feedback. ';
		$mood_prefix .= 'Celebrate their experience and highlight what made it special. Use an upbeat, joyful tone that matches their satisfaction. ';
		$mood_prefix .= 'Encourage them to share their experience and invite them back for future purchases. ';

		return $mood_prefix . $prompt;
	}
}
