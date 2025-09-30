<?php
/**
 * Prompt builder implementation.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM;

use WcAiReviewResponder\LLM\Prompts\Templates\DefaultTemplate;
use WcAiReviewResponder\LLM\Prompts\Templates\EnthusiasticFiveStarTemplate;
use WcAiReviewResponder\LLM\Prompts\Templates\PositiveWithCritiqueTemplate;
use WcAiReviewResponder\LLM\Prompts\Templates\ProductMisunderstandingTemplate;
use WcAiReviewResponder\LLM\Prompts\Templates\DefectiveProductTemplate;
use WcAiReviewResponder\LLM\Prompts\Templates\ShippingIssueTemplate;
use WcAiReviewResponder\LLM\Prompts\Templates\ValuePriceConcernTemplate;
use WcAiReviewResponder\LLM\Prompts\Templates\PromptTemplateInterface;
use WcAiReviewResponder\LLM\Prompts\ReviewContext;
use WcAiReviewResponder\LLM\Prompts\TemplateType;

/**
 * Builds prompts from a well-defined review context using templates.
 */
class PromptBuilder implements BuildPromptInterface {
	/**
	 * Available prompt templates.
	 *
	 * Template types are defined here, in the TemplateType enum, and
	 * also have a class in /LLM/Prompts/Templates/
	 *
	 * @see TemplateType
	 * @var array<string, class-string<PromptTemplateInterface>>
	 */
	private const TEMPLATES = array(
		'default'                  => DefaultTemplate::class,
		'enthusiastic_five_star'   => EnthusiasticFiveStarTemplate::class,
		'positive_with_critique'   => PositiveWithCritiqueTemplate::class,
		'product_misunderstanding' => ProductMisunderstandingTemplate::class,
		'defective_product'        => DefectiveProductTemplate::class,
		'shipping_issue'           => ShippingIssueTemplate::class,
		'value_price_concern'      => ValuePriceConcernTemplate::class,
	);

	/**
	 * Template instances cache.
	 *
	 * @var array<string, PromptTemplateInterface>
	 */
	private array $template_instances = array();

	/**
	 * Build a prompt string from the provided context and template.
	 *
	 * @param array{rating:int,comment:string,product_name:string} $context Review context shape.
	 * @param TemplateType                                         $template Template type to use for building the prompt.
	 * @return string Prompt to send to the AI provider.
	 */
	public function build_prompt( array $context, TemplateType $template = TemplateType::DEFAULT ): string {
		$template_instance = $this->get_template_instance( $template );
		$review_context    = new ReviewContext( $context );

		return $template_instance->get_prompt( $review_context );
	}

	/**
	 * Get a template instance, creating it if it doesn't exist.
	 *
	 * @param TemplateType $template_type Template type.
	 * @return PromptTemplateInterface Template instance.
	 */
	private function get_template_instance( TemplateType $template_type ): PromptTemplateInterface {
		$template_key = $template_type->value;

		if ( ! isset( $this->template_instances[ $template_key ] ) ) {
			$template_class                            = self::TEMPLATES[ $template_key ];
			$this->template_instances[ $template_key ] = new $template_class();
		}

		return $this->template_instances[ $template_key ];
	}

	/**
	 * Get available template types.
	 *
	 * @return array<TemplateType> Array of template types.
	 */
	public function get_available_templates(): array {
		return TemplateType::cases();
	}
}
