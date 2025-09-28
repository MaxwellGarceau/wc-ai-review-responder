<?php
/**
 * Interface for review data access.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Models;

use WcAiReviewResponder\Exceptions\InvalidReviewException;

/**
 * Contract for fetching WooCommerce review context by comment ID.
 */
interface ModelInterface {
	/**
	 * Fetch review context for a given comment ID.
	 *
	 * @param int $comment_id Comment (review) ID.
	 * @return array{comment_id:int,product_id:int,product_name:string,rating:int,comment:string,author:string}
	 * @throws InvalidReviewException When comment is not a valid review.
	 */
	public function get_by_id( int $comment_id ): array;
}
