<?php
/**
 * Review context management class.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\LLM\Prompts;

/**
 * Manages review context data with proper validation and type safety.
 */
class ReviewContext implements ReviewContextInterface {
	/**
	 * The product rating (1-5 stars).
	 *
	 * @var int
	 */
	private int $rating;

	/**
	 * The review comment text.
	 *
	 * @var string
	 */
	private string $comment;

	/**
	 * The product name.
	 *
	 * @var string
	 */
	private string $product_name;

	/**
	 * Constructor.
	 *
	 * @param array{rating:int,comment:string,product_name:string} $context Raw context data.
	 */
	public function __construct( array $context ) {
		$this->rating       = isset( $context['rating'] ) ? (int) $context['rating'] : 0;
		$this->comment      = isset( $context['comment'] ) ? (string) $context['comment'] : '';
		$this->product_name = isset( $context['product_name'] ) ? (string) $context['product_name'] : '';
	}

	/**
	 * Get the product rating.
	 *
	 * @return int The rating (1-5 stars).
	 */
	public function get_rating(): int {
		return $this->rating;
	}

	/**
	 * Get the review comment.
	 *
	 * @return string The review comment text.
	 */
	public function get_comment(): string {
		return $this->comment;
	}

	/**
	 * Get the product name.
	 *
	 * @return string The product name.
	 */
	public function get_product_name(): string {
		return $this->product_name;
	}

	/**
	 * Get formatted rating string.
	 *
	 * @return string Formatted rating (e.g., "5/5 stars").
	 */
	public function get_formatted_rating(): string {
		return $this->rating . '/5 stars';
	}
}
