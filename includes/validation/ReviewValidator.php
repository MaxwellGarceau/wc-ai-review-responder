<?php
/**
 * Review validator for business rule validation.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Validation;

use WcAiReviewResponder\Exceptions\InvalidReviewException;

/**
 * Validates review data against business rules for AI processing.
 *
 * This class handles validation of review data to ensure it meets
 * the requirements for AI response generation.
 */
class ReviewValidator {

	/**
	 * Validate review data for AI processing.
	 *
	 * @param array{comment_id:int,product_id:int,product_name:string,product_description:string,rating:int,comment:string,author:string} $review_data Review data from ReviewModel.
	 * @return void
	 * @throws InvalidReviewException When review data is invalid for AI processing.
	 */
	public function validate_for_ai_processing( array $review_data ): void {
		$comment_content = $review_data['comment'] ?? '';
		$rating          = $review_data['rating'] ?? 0;

		// TODO: mgarceau 2025-09-27: In the future, we will support reviews without ratings and comments
		// by passing more context regarding the user, the product, the order, and any possible difficulties
		// that the user might have encountered.
		if ( '' === trim( $comment_content ) ) {
			throw new InvalidReviewException( 'Review is missing a comment.' );
		}

		if ( '' === (string) $rating ) {
			throw new InvalidReviewException( 'Review is missing a rating.' );
		}

		if ( $rating < 1 || $rating > 5 ) {
			throw new InvalidReviewException( 'Rating must be between 1 and 5.' );
		}
	}
}
