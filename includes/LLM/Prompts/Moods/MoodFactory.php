<?php
/**
 * Mood factory for creating and managing moods.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts\Moods;

use WcAiReviewResponder\LLM\Prompts\ReviewContextInterface;

/**
 * Factory class for creating and managing mood instances.
 */
class MoodFactory {
	/**
	 * Available moods.
	 *
	 * @var array<string, class-string<MoodInterface>>
	 */
	private array $moods = array(
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
		foreach ( $this->moods as $name => $class ) {
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
		if ( ! isset( $this->moods[ $mood_name ] ) ) {
			return null;
		}

		$class = $this->moods[ $mood_name ];
		return new $class();
	}

	/**
	 * Get the default mood.
	 *
	 * @return MoodInterface The default mood.
	 */
	public function get_default_mood(): MoodInterface {
		$moods = $this->get_all_moods();
		return $moods['empathetic_problem_solver'];
	}

	/**
	 * Get mood names and descriptions.
	 *
	 * @return array<string, string> Array of mood names and descriptions.
	 */
	public function get_mood_descriptions(): array {
		$descriptions = array();
		foreach ( $this->get_all_moods() as $name => $mood ) {
			$descriptions[ $name ] = $mood->get_description();
		}
		return $descriptions;
	}
}
