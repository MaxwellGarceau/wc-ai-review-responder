<?php
/**
 * Interface for building AI prompts from review context.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder;

interface Build_Prompt_Interface {
	/**
	 * Build a prompt string from the provided context.
	 *
	 * @param array{rating:int,comment:string,product_name:string} $context Review context shape.
	 * @return string Prompt to send to the AI provider.
	 */
	public function build_prompt( array $context ): string;
}
