<?php
/**
 * Exception thrown when the AI provider fails to return a valid response.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Exceptions;

/**
 * Exception class thrown when the AI provider fails to return a valid response.
 */
class AiResponseFailure extends \RuntimeException {
	/**
	 * Debug context information.
	 *
	 * @var array<string,mixed>
	 */
	private $debug_context = array();

	/**
	 * Constructor.
	 *
	 * @param string              $message       Error message.
	 * @param int                 $code          Error code.
	 * @param \Throwable|null     $previous      Previous exception.
	 * @param array<string,mixed> $debug_context Additional debug context.
	 */
	public function __construct( $message = '', $code = 0, $previous = null, $debug_context = array() ) {
		parent::__construct( $message, $code, $previous );
		$this->debug_context = $debug_context;
	}

	/**
	 * Get debug context information.
	 *
	 * @return array<string,mixed> Debug context data.
	 */
	public function get_debug_context() {
		return $this->debug_context;
	}
}
