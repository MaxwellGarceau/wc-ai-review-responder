<?php
/**
 * Mood types enum for type-safe mood selection.
 *
 * Mood types are defined here and in the MoodFactory class
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Moods;

/**
 * Enum for defining available mood types.
 */
enum MoodsType: string {
	case EMPATHETIC_PROBLEM_SOLVER = 'empathetic_problem_solver';
	case ENTHUSIASTIC_APPRECIATOR  = 'enthusiastic_appreciator';
	case PROFESSIONAL_EDUCATOR     = 'professional_educator';
}
