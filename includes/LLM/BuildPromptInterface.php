<?php
/**
 * Interface for building AI prompts from review context.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM;

use WcAiReviewResponder\LLM\Prompts\TemplateType;
use WcAiReviewResponder\LLM\Prompts\Moods\MoodsType;

interface BuildPromptInterface {
	/**
	 * Build a prompt string from the provided context and template.
	 *
	 * @param array{rating:int,comment:string,product_name:string} $context Review context shape.
	 * @param TemplateType                                         $template Template type to use for building the prompt.
	 * @param MoodsType                                            $mood Mood type to use for building the prompt.
	 * @return string Prompt to send to the AI provider.
	 */
	public function build_prompt( array $context, TemplateType $template = TemplateType::DEFAULT, MoodsType $mood = MoodsType::EMPATHETIC_PROBLEM_SOLVER ): string;
}
