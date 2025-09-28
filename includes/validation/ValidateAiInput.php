<?php
/**
 * AI input validator: validates, normalizes, and sanitizes user review input
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
 * Validate and sanitize the AI input context.
 */
class ValidateAiInput {
	/**
	 * Validate and normalize the review input.
	 *
	 * Expects an associative array with the following keys:
	 * - rating (int)
	 * - comment (string)
	 * - product_name (string)
	 *
	 * Returns a cleaned version of the same shape.
	 *
	 * @param array{rating:int|mixed,comment:string|mixed,product_name:string|mixed} $context Raw review context.
	 * @return array{rating:int,comment:string,product_name:string} Cleaned context.
	 */
	public function validate( array $context ): array {
		$rating      = isset( $context['rating'] ) ? (int) $context['rating'] : 0;
		$raw_comment = isset( $context['comment'] ) ? (string) $context['comment'] : '';
		$raw_product = isset( $context['product_name'] ) ? (string) $context['product_name'] : '';

		// Clamp rating to expected WooCommerce range.
		if ( $rating < 1 || $rating > 5 ) {
			$rating = max( 1, min( 5, $rating ) );
		}

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
		$text = strip_shortcodes( $text );
		$text = wp_strip_all_tags( $text, false );
		$text = wp_specialchars_decode( $text, ENT_QUOTES );

		// Remove control characters except newlines and tabs.
		$text = preg_replace( '/[^\P{C}\n\t]+/u', '', $text );

		// Collapse runs of spaces/tabs; preserve newlines.
		$text = preg_replace( '/[ \t]+/u', ' ', $text );

		// Trim surrounding whitespace.
		$text = trim( $text );

		// Optional lightweight PII redaction.
		$text = preg_replace( '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[redacted-email]', $text );
		$text = preg_replace( '/https?:\/\/\S+/i', '[redacted-url]', $text );

		// Enforce a conservative character cap to control token usage.
		$max_chars = 8000;
		if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ) {
			if ( mb_strlen( $text, 'UTF-8' ) > $max_chars ) {
				$text = mb_substr( $text, 0, $max_chars, 'UTF-8' ) . '…';
			}
		} elseif ( strlen( $text ) > $max_chars ) {
			$text = substr( $text, 0, $max_chars ) . '…';
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
		$text = strip_shortcodes( $text );
		$text = wp_strip_all_tags( $text, true );
		$text = wp_specialchars_decode( $text, ENT_QUOTES );
		$text = preg_replace( '/\s+/u', ' ', $text );
		return trim( $text );
	}
}
