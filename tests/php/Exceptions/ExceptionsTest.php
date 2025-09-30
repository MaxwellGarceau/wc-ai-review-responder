<?php
/**
 * Exceptions test cases.
 *
 * Added by assistant.
 *
 * @package WcAiReviewResponder
 */

/**
 * Test exception classes.
 */
class ExceptionsTest extends WP_UnitTestCase {

	/**
	 * Test AiResponseFailure debug context handling.
	 */
	public function test_ai_response_failure_debug_context() {
		$ctx = array( 'prompt' => 'P', 'code' => 500 );
		$e   = new \WcAiReviewResponder\Exceptions\AiResponseFailure( 'msg', 500, null, $ctx );
		$this->assertSame( $ctx, $e->get_debug_context() );
	}

	/**
	 * Test RateLimitExceededException getters.
	 */
	public function test_rate_limit_exceeded_exception_getters() {
		$reset = time() + 3600;
		$e     = new \WcAiReviewResponder\Exceptions\RateLimitExceededException( 'rl', $reset );
		$this->assertSame( $reset, $e->get_reset_timestamp() );
		$this->assertIsString( $e->get_reset_time() );
	}

	/**
	 * Test InvalidArgumentsException can be constructed.
	 */
	public function test_invalid_arguments_exception_construct() {
		$e = new \WcAiReviewResponder\Exceptions\InvalidArgumentsException( 'bad' );
		$this->assertInstanceOf( \InvalidArgumentException::class, $e );
	}

	/**
	 * Test InvalidReviewException can be constructed.
	 */
	public function test_invalid_review_exception_construct() {
		$e = new \WcAiReviewResponder\Exceptions\InvalidReviewException( 'bad' );
		$this->assertInstanceOf( \RuntimeException::class, $e );
	}
}


