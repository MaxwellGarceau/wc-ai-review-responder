<?php
/**
 * Error type enum for type safety.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Enums;

/**
 * Error type enum for type safety.
 *
 * @since 1.0.0
 */
enum ErrorType: string {
	case UNAUTHORIZED        = 'unauthorized';
	case INVALID_NONCE       = 'invalid_nonce';
	case INVALID_REVIEW      = 'invalid_review';
	case RATE_LIMIT_EXCEEDED = 'rate_limit_exceeded';
	case AI_FAILURE          = 'ai_failure';
}
