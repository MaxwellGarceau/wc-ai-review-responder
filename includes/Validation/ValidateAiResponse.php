<?php
/**
 * AI response validator implementation to validate and sanitize AI responses.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Validation;

/**
 * Generates the final sanitized reply string from a raw AI response.
 */
class ValidateAiResponse implements ValidateAiResponseInterface {
	/**
	 * Validate and sanitize the AI response string.
	 *
	 * @param string $ai_response Raw AI response string.
	 * @return string Sanitized, validated reply.
	 */
	public function validate( string $ai_response ): string {
		$reply = is_string( $ai_response ) ? trim( $ai_response ) : '';
		if ( '' === $reply ) {
			return '';
		}

		// Sanitize for safe admin insertion.
		return wp_kses_post( $reply );
	}
}
