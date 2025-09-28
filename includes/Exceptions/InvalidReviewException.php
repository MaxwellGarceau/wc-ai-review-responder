<?php
/**
 * Exception thrown when a review is missing required data (rating and/or comment).
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Exceptions;

/**
 * Exception class thrown when a review is missing required data (rating and/or comment).
 */
class InvalidReviewException extends \RuntimeException {
}
