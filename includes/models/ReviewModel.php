<?php
/**
 * Review model for extracting and validating WooCommerce review data.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Models;

use WcAiReviewResponder\Exceptions\InvalidReviewException;

/**
 * Review model class for extracting and validating WooCommerce review data.
 *
 * This is the single source of truth for accessing the DB to query review data.
 */
class ReviewModel {
	/**
	 * Fetch review context for a given comment ID.
	 *
	 * @param int $comment_id Comment (review) ID.
	 * @return array{comment_id:int,product_id:int,product_name:string,rating:int,comment:string,author:string}
	 * @throws InvalidReviewException When comment is not a valid review.
	 */
	public function get_by_id( int $comment_id ): array {
		$comment = get_comment( $comment_id );
		if ( ! $comment || 'review' !== get_comment_type( $comment ) ) {
			throw new InvalidReviewException( 'Comment is not a WooCommerce product review.' );
		}

		$product_id = (int) $comment->comment_post_ID;
		$rating     = get_comment_meta( $comment_id, 'rating', true );
		$content    = (string) $comment->comment_content;

		if ( '' === trim( $content ) ) {
			throw new InvalidReviewException( 'Review is missing a comment.' );
		}

		if ( '' === (string) $rating ) {
			throw new InvalidReviewException( 'Review is missing a rating.' );
		}

		$product_name = get_the_title( $product_id );

		return array(
			'comment_id'   => (int) $comment_id,
			'product_id'   => (int) $product_id,
			'product_name' => is_string( $product_name ) ? $product_name : '',
			'rating'       => (int) $rating,
			'comment'      => $content,
			'author'       => (string) $comment->comment_author,
		);
	}
}
