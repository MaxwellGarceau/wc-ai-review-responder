<?php
/**
 * Professional Educator mood for confused/misinformed reviews.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Moods;

use WcAiReviewResponder\LLM\Prompts\ReviewContextInterface;

/**
 * Mood for handling confused or misinformed reviews with professional education.
 */
class ProfessionalEducator implements MoodInterface {


	/**
	 * Apply mood to a prompt.
	 *
	 * @param string                 $prompt The base prompt.
	 * @param ReviewContextInterface $context Review context object.
	 * @return string The prompt with mood applied.
	 */
	public function apply( string $prompt, ReviewContextInterface $context ): string {
		$mood_prefix  = 'Write with patience and professionalism. Address any misconceptions clearly and helpfully. ';
		$mood_prefix .= 'Provide clear, accurate information without being condescending. Use an informative, supportive tone. ';
		$mood_prefix .= 'Focus on education and clarification while maintaining respect for the customer\'s perspective. ';

		return $mood_prefix . $prompt;
	}
}
