<?php
/**
 * Dependency injection container factory.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Core;

use DI\ContainerBuilder;
use WcAiReviewResponder\LLM\Prompt_Builder;
use WcAiReviewResponder\Validation\Validate_AI_Response;
use WcAiReviewResponder\Clients\AI_Client;

/**
 * Factory class for creating and configuring the dependency injection container.
 */
class Container_Factory {

	/**
	 * Build and configure the dependency injection container.
	 *
	 * @return \DI\Container The configured container.
	 */
	public function build() {
		$builder = new ContainerBuilder();
		$builder->useAnnotations( false );
		$builder->addDefinitions(
			array(
				WcAiReviewResponder\LLM\Build_Prompt_Interface::class => \DI\get( Prompt_Builder::class ),
				WcAiReviewResponder\Validation\Validate_AI_Response_Interface::class => \DI\get( Validate_AI_Response::class ),
				\WcAiReviewResponder\Clients\AI_Client::class => \DI\autowire()->constructor( \DI\env( 'GEMINI_API_KEY' ) ),
			)
		);
		return $builder->build();
	}
}
