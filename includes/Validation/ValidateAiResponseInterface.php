<?php
/**
 * Interface for validating AI responses to ensure they are valid replies.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Validation;

interface ValidateAiResponseInterface {
	/**
	 * Validate the AI response string.
	 *
	 * @param string $ai_response Raw AI response string.
	 * @return string Sanitized, validated reply.
	 */
	public function validate( string $ai_response ): string;
}
