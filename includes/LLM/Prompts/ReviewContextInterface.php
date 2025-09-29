<?php
/**
 * Interface for review context data.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts;

/**
 * Interface that defines the required properties and methods for review context.
 */
interface ReviewContextInterface {
	/**
	 * Get the product rating.
	 *
	 * @return int The rating (1-5 stars).
	 */
	public function get_rating(): int;

	/**
	 * Get the review comment.
	 *
	 * @return string The review comment text.
	 */
	public function get_comment(): string;

	/**
	 * Get the product name.
	 *
	 * @return string The product name.
	 */
	public function get_product_name(): string;

	/**
	 * Get formatted rating string.
	 *
	 * @return string Formatted rating (e.g., "5/5 stars").
	 */
	public function get_formatted_rating(): string;
}
