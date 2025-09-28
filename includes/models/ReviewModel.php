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
class ReviewModel implements ModelInterface {
	/**
	 * Fetch review context for a given comment ID.
	 *
	 * @param int $comment_id Comment (review) ID.
	 * @return array{comment_id:int,product_id:int,product_name:string,product_description:string,rating:int,comment:string,author:string}
	 * @throws InvalidReviewException When comment is not a valid review.
	 */
	public function get_by_id( int $comment_id ): array {
		$comment = get_comment( $comment_id );
		if ( ! $comment || 'review' !== get_comment_type( $comment ) ) {
			throw new InvalidReviewException( 'Comment is not a WooCommerce product review.' );
		}

		$product_id         = (int) $comment->comment_post_ID;
		$rating             = get_comment_meta( $comment_id, 'rating', true );
		$comment_content    = (string) $comment->comment_content;

		if ( '' === trim( $comment_content ) ) {
			throw new InvalidReviewException( 'Review is missing a comment.' );
		}

		if ( '' === (string) $rating ) {
			throw new InvalidReviewException( 'Review is missing a rating.' );
		}

		$product_name        = get_the_title( $product_id );
		$product_description = $this->get_product_description( $product_id );

		return array(
			'comment_id'          => (int) $comment_id,
			'product_id'          => (int) $product_id,
			'product_name'        => is_string( $product_name ) ? $product_name : '',
			'product_description' => is_string( $product_description ) ? $product_description : '',
			'rating'              => (int) $rating,
			'comment'             => $comment_content,
			'author'              => (string) $comment->comment_author,
		);
	}

	/**
	 * Get product description with fallback to short description.
	 *
	 * @param int $product_id Product ID.
	 * @return string Product description or empty string if none available.
	 */
	private function get_product_description( int $product_id ): string {
		$excerpt = get_the_excerpt( $product_id );
		if ( empty( $excerpt ) ) {
			$product = wc_get_product( $product_id );
			$excerpt = $product ? $product->get_short_description() : '';
		}
		return $excerpt;
	}
}
