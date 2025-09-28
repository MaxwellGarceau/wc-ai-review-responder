<?php
/**
 * Exception thrown when rate limit is exceeded.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Exceptions;

/**
 * Exception class for rate limit exceeded errors.
 */
class RateLimitExceededException extends \Exception {

	/**
	 * Timestamp when the rate limit resets.
	 *
	 * @var int
	 */
	private $reset_timestamp;

	/**
	 * Constructor.
	 *
	 * @param string     $message        Exception message.
	 * @param int        $reset_timestamp Timestamp when rate limit resets.
	 * @param int        $code           Exception code.
	 * @param \Exception $previous       Previous exception.
	 */
	public function __construct( string $message = '', int $reset_timestamp = 0, int $code = 0, \Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
		$this->reset_timestamp = $reset_timestamp;
	}

	/**
	 * Get the timestamp when the rate limit resets.
	 *
	 * @return int Reset timestamp.
	 */
	public function get_reset_timestamp(): int {
		return $this->reset_timestamp;
	}

	/**
	 * Get the formatted reset time.
	 *
	 * @return string Formatted reset time.
	 */
	public function get_reset_time(): string {
		return wp_date( 'Y-m-d H:i:s', $this->reset_timestamp );
	}
}
