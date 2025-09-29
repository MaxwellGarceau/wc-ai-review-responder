<?php
/**
 * Enum for prompt template types.
 *
 * Template types are defined here, in the PromptBuilder class,
 * and also have a class in /LLM/Prompts/Templates/
 *
 * @see PromptBuilder
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts;

/**
 * Enum representing available prompt template types.
 */
enum TemplateType: string {
	case DEFAULT                  = 'default';
	case ENTHUSIASTIC_FIVE_STAR   = 'enthusiastic_five_star';
	case POSITIVE_WITH_CRITIQUE   = 'positive_with_critique';
	case PRODUCT_MISUNDERSTANDING = 'product_misunderstanding';
	case DEFECTIVE_PRODUCT        = 'defective_product';
	case SHIPPING_ISSUE           = 'shipping_issue';
}
