<?php

namespace WcAiReviewResponder;

use WcAiReviewResponder\Exceptions\Invalid_Review_Exception;

/**
 * Extracts and validates review data from WordPress/WooCommerce.
 */
class Review_Handler {
	/**
	 * Fetch review context for a given comment ID.
	 *
	 * @param int $commentId Comment (review) ID.
	 * @return array<string,mixed>
	 * @throws Invalid_Review_Exception
	 */
	public function get_review_context( $commentId ) {
		$comment = get_comment( $commentId );
		if ( ! $comment || 'review' !== get_comment_type( $comment ) ) {
			throw new Invalid_Review_Exception( 'Comment is not a WooCommerce product review.' );
		}

		$productId = (int) $comment->comment_post_ID;
		$rating    = get_comment_meta( $commentId, 'rating', true );
		$content   = (string) $comment->comment_content;

		if ( '' === trim( $content ) ) {
			throw new Invalid_Review_Exception( 'Review is missing a comment.' );
		}

		if ( '' === (string) $rating ) {
			throw new Invalid_Review_Exception( 'Review is missing a rating.' );
		}

		$productName = get_the_title( $productId );

		return array(
			'comment_id'   => (int) $commentId,
			'product_id'   => (int) $productId,
			'product_name' => is_string( $productName ) ? $productName : '',
			'rating'       => (int) $rating,
			'comment'      => $content,
			'author'       => (string) $comment->comment_author,
		);
	}
}
