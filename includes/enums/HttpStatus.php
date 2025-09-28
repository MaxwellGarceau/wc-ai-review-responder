<?php
/**
 * HTTP status code enum for type safety and clarity.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Enums;

/**
 * HTTP status code enum for type safety and clarity.
 *
 * @since 1.0.0
 */
enum HttpStatus: int {
	case UNAUTHORIZED          = 401;
	case FORBIDDEN             = 403;
	case BAD_REQUEST           = 400;
	case TOO_MANY_REQUESTS     = 429;
	case INTERNAL_SERVER_ERROR = 500;
}
