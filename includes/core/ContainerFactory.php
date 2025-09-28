<?php
/**
 * Dependency injection container factory.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Core;

use DI\ContainerBuilder;

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
		$builder->addDefinitions(
			array(
				// Load environment variables.
				\WcAiReviewResponder\Clients\AiClient::class => \DI\autowire()->constructor( \DI\env( 'GEMINI_API_KEY', 'test-key' ), \DI\get( \WcAiReviewResponder\Clients\Request::class ) ),

				// Resolve interfaces to concrete implementations.
				\WcAiReviewResponder\CLI\AiReviewCli::class => \DI\create()
					->constructor(
						\DI\get( \WcAiReviewResponder\Models\ReviewModel::class ),
						\DI\get( \WcAiReviewResponder\LLM\PromptBuilder::class ),
						\DI\get( \WcAiReviewResponder\Clients\AiClient::class ),
						\DI\get( \WcAiReviewResponder\Validation\ValidateAiResponse::class )
					),
				\WcAiReviewResponder\Endpoints\AjaxHandler::class => \DI\create()
					->constructor(
						\DI\get( \WcAiReviewResponder\Models\ReviewModel::class ),
						\DI\get( \WcAiReviewResponder\LLM\PromptBuilder::class ),
						\DI\get( \WcAiReviewResponder\Clients\AiClient::class ),
						\DI\get( \WcAiReviewResponder\Validation\ValidateAiResponse::class )
					),
			)
		);
		return $builder->build();
	}
}
