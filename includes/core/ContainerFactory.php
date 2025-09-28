<?php
/**
 * Dependency injection container factory.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Core;

use DI\ContainerBuilder;
use WcAiReviewResponder\LLM\PromptBuilder;
use WcAiReviewResponder\Validation\ValidateAiResponse;
use WcAiReviewResponder\Clients\AiClient;

/**
 * Factory class for creating and configuring the dependency injection container.
 */
class ContainerFactory {

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
				WcAiReviewResponder\LLM\BuildPromptInterface::class => \DI\get( PromptBuilder::class ),
				WcAiReviewResponder\Validation\ValidateAiResponseInterface::class => \DI\get( ValidateAiResponse::class ),
				\WcAiReviewResponder\Clients\AiClient::class => \DI\autowire()->constructor( \DI\env( 'GEMINI_API_KEY' ) ),
			)
		);
		return $builder->build();
	}
}
