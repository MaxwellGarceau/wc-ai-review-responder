<?php
/**
 * Enums test cases for ErrorType and HttpStatus.
 *
 * Added by assistant.
 *
 * @package WcAiReviewResponder
 */

/**
 * Test the enums.
 */
class EnumsTest extends WP_UnitTestCase {

	public function test_error_type_values() {
		$values = array_map( fn( $c ) => $c->value, \WcAiReviewResponder\Enums\ErrorType::cases() );
		$this->assertContains( 'unauthorized', $values );
		$this->assertContains( 'invalid_nonce', $values );
		$this->assertContains( 'invalid_review', $values );
		$this->assertContains( 'rate_limit_exceeded', $values );
		$this->assertContains( 'ai_failure', $values );
	}

	public function test_http_status_values() {
		$this->assertSame( 401, \WcAiReviewResponder\Enums\HttpStatus::UNAUTHORIZED->value );
		$this->assertSame( 403, \WcAiReviewResponder\Enums\HttpStatus::FORBIDDEN->value );
		$this->assertSame( 400, \WcAiReviewResponder\Enums\HttpStatus::BAD_REQUEST->value );
		$this->assertSame( 429, \WcAiReviewResponder\Enums\HttpStatus::TOO_MANY_REQUESTS->value );
		$this->assertSame( 500, \WcAiReviewResponder\Enums\HttpStatus::INTERNAL_SERVER_ERROR->value );
	}
}


