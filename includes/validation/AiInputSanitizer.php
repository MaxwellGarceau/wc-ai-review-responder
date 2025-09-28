<?php
/**
 * AI input sanitizer: normalizes and sanitizes user review input
 * before building prompts for the AI provider.
 *
 * WordPress sanitizes on input and escapes on output, but comments can still
 * contain limited HTML, entities, shortcodes, odd whitespace, and PII. These
 * need to be normalized and sanitized before sending it to the AI provider.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Validation;

/**
 * Sanitize and normalize the AI input context.
 */
class AiInputSanitizer {
	/**
	 * Maximum character limit for AI input text to control token usage.
	 *
	 * @var int
	 */
	private const MAX_CHARS = 8000;
	/**
	 * Sanitize and normalize the review input for AI processing.
	 *
	 * Expects an associative array with the following keys:
	 * - rating (int)
	 * - comment (string)
	 * - product_name (string)
	 *
	 * Returns a cleaned version of the same shape.
	 *
	 * @param array{rating:int|mixed,comment:string|mixed,product_name:string|mixed} $context Raw review context.
	 * @return array{rating:int,comment:string,product_name:string} Sanitized context.
	 */
	public function sanitize( array $context ): array {
		$rating      = isset( $context['rating'] ) ? (int) $context['rating'] : 0;
		$raw_comment = isset( $context['comment'] ) ? (string) $context['comment'] : '';
		$raw_product = isset( $context['product_name'] ) ? (string) $context['product_name'] : '';

		// Normalize and sanitize the comment for AI consumption (not for HTML output).
		$comment = $this->normalize_text_for_ai( $raw_comment );

		// Normalize product name into a compact, single-line string for prompts.
		$product = $this->normalize_inline_text( $raw_product );

		return array(
			'rating'       => $rating,
			'comment'      => $comment,
			'product_name' => $product,
		);
	}

	/**
	 * Normalize general multi-line text for AI: remove shortcodes/HTML, decode entities,
	 * normalize whitespace and control characters, optionally redact PII, and cap length.
	 *
	 * Note: This is used only for outbound AI requests; we do not store this back in DB.
	 *
	 * @param string $text Input text.
	 * @return string Cleaned text.
	 */
	private function normalize_text_for_ai( string $text ): string {
		$text = $this->sanitize_basic_text( $text, false );

		// Remove control characters except newlines and tabs.
		// Example: "Hello\x00World\x01\x02Test" becomes "HelloWorldTest".
		$text = preg_replace( '/[^\P{C}\n\t]+/u', '', $text );

		// Optional lightweight PII redaction.
		// Example: "Contact me at john@example.com or visit https://example.com" becomes
		// "Contact me at [redacted-email] or visit [redacted-url]".
		$text = preg_replace( '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[redacted-email]', $text );
		$text = preg_replace( '/https?:\/\/\S+/i', '[redacted-url]', $text );

		// Enforce a conservative character cap to control token usage.
		if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ) {
			if ( mb_strlen( $text, 'UTF-8' ) > self::MAX_CHARS ) {
				$text = mb_substr( $text, 0, self::MAX_CHARS, 'UTF-8' ) . '…';
			}
		} elseif ( strlen( $text ) > self::MAX_CHARS ) {
			$text = substr( $text, 0, self::MAX_CHARS ) . '…';
		}

		return $text;
	}

	/**
	 * Normalize a short, inline string (e.g., product name) for prompt usage.
	 * Strips tags/shortcodes, collapses whitespace to single spaces, and trims.
	 *
	 * @param string $text Raw inline text.
	 * @return string Clean inline text.
	 */
	private function normalize_inline_text( string $text ): string {
		$text = $this->sanitize_basic_text( $text, true );
		return trim( $text );
	}

	/**
	 * Basic text sanitization: remove shortcodes, HTML tags, decode entities, and normalize whitespace.
	 *
	 * @param string $text        Input text.
	 * @param bool   $remove_line_breaks Whether to remove line breaks (true for inline text).
	 * @return string Sanitized text.
	 */
	private function sanitize_basic_text( string $text, bool $remove_line_breaks = false ): string {
		// Remove shortcodes.
		$text = strip_shortcodes( $text );

		// Remove HTML tags.
		$text = wp_strip_all_tags( $text, $remove_line_breaks );

		// Decode HTML entities.
		$text = wp_specialchars_decode( $text, ENT_QUOTES );

		// Normalize whitespace.
		$text = normalize_whitespace( $text );

		return $text;
	}
}
