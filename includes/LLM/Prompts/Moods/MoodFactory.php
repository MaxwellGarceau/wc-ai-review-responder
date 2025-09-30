<?php
/**
 * Mood factory for creating and managing moods.
 *
 * Mood types are defined here and in the MoodsType enum
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
	 * Get all available moods.
	 *
	 * @return array<string, MoodInterface> Array of mood instances.
	 */
	public function get_all_moods(): array {
		$mood_instances = array();
		foreach ( self::MOODS as $name => $class ) {
			$mood_instances[ $name ] = new $class();
		}
		return $mood_instances;
	}

	/**
	 * Get a specific mood by name.
	 *
	 * @param string $mood_name The mood name.
	 * @return MoodInterface|null The mood instance or null if not found.
	 */
	public function get_mood( string $mood_name ): ?MoodInterface {
		if ( ! isset( self::MOODS[ $mood_name ] ) ) {
			return null;
		}

		$class = self::MOODS[ $mood_name ];
		return new $class();
	}

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

	/**
	 * Get the default mood.
	 *
	 * @return MoodInterface The default mood.
	 */
	public function get_default_mood(): MoodInterface {
		return $this->get_mood_by_type( MoodsType::EMPATHETIC_PROBLEM_SOLVER );
	}


	/**
	 * Get available mood types.
	 *
	 * @return array<MoodsType> Array of mood types.
	 */
	public function get_available_mood_types(): array {
		return MoodsType::cases();
	}
}
