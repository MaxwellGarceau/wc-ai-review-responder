<?php
/**
 * Empathetic Problem-Solver mood for negative reviews.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Moods;

use WcAiReviewResponder\LLM\Prompts\ReviewContextInterface;

/**
 * Mood for handling negative reviews with empathy and problem-solving focus.
 */
class EmpatheticProblemSolver implements MoodInterface {
	/**
	 * Get the mood name.
	 *
	 * @return string The mood name.
	 */
	public function get_name(): string {
		return 'empathetic_problem_solver';
	}


	/**
	 * Apply mood to a prompt.
	 *
	 * @param string                 $prompt The base prompt.
	 * @param ReviewContextInterface $context Review context object.
	 * @return string The prompt with mood applied.
	 */
	public function apply( string $prompt, ReviewContextInterface $context ): string {
		$mood_prefix  = 'Write with genuine empathy and understanding. Acknowledge the customer\'s frustration and validate their experience. ';
		$mood_prefix .= 'Focus on finding solutions and making things right. Use a warm, caring tone that shows you truly care about their satisfaction. ';
		$mood_prefix .= 'Demonstrate accountability and commitment to improvement. ';

		return $mood_prefix . $prompt;
	}
}
