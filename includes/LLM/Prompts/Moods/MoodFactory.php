<?php
/**
 * Mood factory for creating and managing moods.
 *
 * Mood types are defined here and in the MoodsType enum.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Moods;

/**
 * Factory class for creating and managing mood instances.
 */
class MoodFactory {
	/**
	 * Available moods.
	 *
	 * @var array<string, class-string<MoodInterface>>
	 */
	private const MOODS = array(
		'empathetic_problem_solver' => EmpatheticProblemSolver::class,
		'enthusiastic_appreciator'  => EnthusiasticAppreciator::class,
		'professional_educator'     => ProfessionalEducator::class,
	);

	/**
	 * Get a specific mood by type.
	 *
	 * @param MoodsType $mood_type The mood type.
	 * @return MoodInterface The mood instance.
	 */
	public function get_mood_by_type( MoodsType $mood_type ): MoodInterface {
		$class = self::MOODS[ $mood_type->value ];
		return new $class();
	}

}
