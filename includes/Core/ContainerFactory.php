<?php
/**
 * Dependency injection container factory.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Core;

use DI\ContainerBuilder;
use WcAiReviewResponder\Admin\ReviewActions;
use WcAiReviewResponder\CLI\AiReviewCli;
use WcAiReviewResponder\Clients\GeminiClientFactory;
use WcAiReviewResponder\Clients\Request;
use WcAiReviewResponder\Endpoints\AjaxHandler;
use WcAiReviewResponder\LLM\PromptBuilder;
use WcAiReviewResponder\Localization\Localizations;
use WcAiReviewResponder\Models\ReviewModel;
use WcAiReviewResponder\RateLimiting\RateLimiter;
use WcAiReviewResponder\Validation\AiInputSanitizer;
use WcAiReviewResponder\Validation\ReviewValidator;
use WcAiReviewResponder\Validation\ValidateAiResponse;

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
				GeminiClientFactory::class => \DI\autowire()->constructor( \DI\env( 'GEMINI_API_KEY', 'test-key' ), \DI\get( Request::class ), \DI\get( RateLimiter::class ) ),

				// Localization service.
				Localizations::class       => \DI\autowire(),

				// Resolve interfaces to concrete implementations.
				AiReviewCli::class         => \DI\create()
					->constructor(
						\DI\get( ReviewModel::class ),
						\DI\get( PromptBuilder::class ),
						\DI\get( GeminiClientFactory::class ),
						\DI\get( ValidateAiResponse::class ),
						\DI\get( ReviewValidator::class ),
						\DI\get( AiInputSanitizer::class ),
						\DI\get( Localizations::class )
					),
				ReviewValidator::class     => \DI\create()
					->constructor( \DI\get( Localizations::class ) ),
				ReviewActions::class       => \DI\create()
					->constructor( \DI\get( Localizations::class ) ),
				AjaxHandler::class         => \DI\create()
					->constructor(
						\DI\get( ReviewModel::class ),
						\DI\get( PromptBuilder::class ),
						\DI\get( GeminiClientFactory::class ),
						\DI\get( ValidateAiResponse::class ),
						\DI\get( AiInputSanitizer::class ),
						\DI\get( ReviewValidator::class )
					),
			)
		);
		return $builder->build();
	}
}
