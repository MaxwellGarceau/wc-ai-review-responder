<?php
/**
 * GeminiClient core behavior test cases.
 *
 * These tests focus on request/response handling, configuration merge, and
 * error conditions not covered by the rate-limiting integration tests.
 *
 * @package WcAiReviewResponder
 */

use WcAiReviewResponder\Clients\GeminiClient;

/**
 * Test the GeminiClient class core behaviors.
 */
class GeminiClientTest extends WP_UnitTestCase {

	/**
	 * Test that missing API key throws AiResponseFailure early.
	 */
	public function test_missing_api_key_throws_exception() {
		/** @var \WcAiReviewResponder\Clients\Request&PHPUnit\Framework\MockObject\MockObject $mock_request */
		$mock_request      = $this->createMock( \WcAiReviewResponder\Clients\Request::class );
		/** @var \WcAiReviewResponder\RateLimiting\RateLimiter&PHPUnit\Framework\MockObject\MockObject $mock_rate_limiter */
		$mock_rate_limiter = $this->createMock( \WcAiReviewResponder\RateLimiting\RateLimiter::class );

		$client = new GeminiClient( '', $mock_request, $mock_rate_limiter );

		$this->expectException( \WcAiReviewResponder\Exceptions\AiResponseFailure::class );
		$client->get( 'Test prompt' );
	}

	/**
	 * Test that invalid response format throws AiResponseFailure.
	 *
	 * The Gemini response must contain candidates[0].content.parts[0].text.
	 */
	public function test_invalid_response_format_throws_exception() {
		/** @var \WcAiReviewResponder\RateLimiting\RateLimiter&PHPUnit\Framework\MockObject\MockObject $mock_rate_limiter */
		$mock_rate_limiter = $this->createMock( \WcAiReviewResponder\RateLimiting\RateLimiter::class );
		$mock_rate_limiter->method( 'check_rate_limit' );
		$mock_rate_limiter->method( 'record_request' );

		/** @var \WcAiReviewResponder\Clients\Request&PHPUnit\Framework\MockObject\MockObject $mock_request */
		$mock_request = $this->createMock( \WcAiReviewResponder\Clients\Request::class );
		$mock_request->method( 'post' )->willReturn( array( 'candidates' => array() ) );

		$client = new GeminiClient( 'test', $mock_request, $mock_rate_limiter );

		$this->expectException( \WcAiReviewResponder\Exceptions\AiResponseFailure::class );
		$client->get( 'Test prompt' );
	}

	/**
	 * Test that a successful response returns the extracted text content.
	 */
	public function test_successful_response_returns_text() {
		/** @var \WcAiReviewResponder\RateLimiting\RateLimiter&PHPUnit\Framework\MockObject\MockObject $mock_rate_limiter */
		$mock_rate_limiter = $this->createMock( \WcAiReviewResponder\RateLimiting\RateLimiter::class );
		$mock_rate_limiter->expects( $this->once() )->method( 'check_rate_limit' );
		$mock_rate_limiter->expects( $this->once() )->method( 'record_request' );

		/** @var \WcAiReviewResponder\Clients\Request&PHPUnit\Framework\MockObject\MockObject $mock_request */
		$mock_request = $this->createMock( \WcAiReviewResponder\Clients\Request::class );
		$mock_request->expects( $this->once() )
			->method( 'post' )
			->willReturn( array(
				'candidates' => array(
					array(
						'content' => array(
							'parts' => array(
								array( 'text' => 'Hello World' ),
							),
						),
					),
				),
			) );

		$client   = new GeminiClient( 'test', $mock_request, $mock_rate_limiter );
		$response = $client->get( 'Prompt' );
		$this->assertSame( 'Hello World', $response );
	}

	/**
	 * Test that custom config is merged with defaults and forwarded in the request body.
	 */
	public function test_custom_config_is_merged_and_forwarded() {
		$captured_body = null;

		/** @var \WcAiReviewResponder\RateLimiting\RateLimiter&PHPUnit\Framework\MockObject\MockObject $mock_rate_limiter */
		$mock_rate_limiter = $this->createMock( \WcAiReviewResponder\RateLimiting\RateLimiter::class );
		$mock_rate_limiter->method( 'check_rate_limit' );
		$mock_rate_limiter->method( 'record_request' );

		/** @var \WcAiReviewResponder\Clients\Request&PHPUnit\Framework\MockObject\MockObject $mock_request */
		$mock_request = $this->getMockBuilder( \WcAiReviewResponder\Clients\Request::class )
			->onlyMethods( array( 'post' ) )
			->getMock();

		$mock_request->method( 'post' )
			->willReturnCallback( function( $url, $api_key, $body ) use ( &$captured_body ) {
				$captured_body = $body;
				return array(
					'candidates' => array(
						array(
							'content' => array(
								'parts' => array(
									array( 'text' => 'ok' ),
								),
							),
						),
					),
				);
			} );

		$config = array(
			'response_mime_type' => 'application/json',
			'temperature'        => 0.2,
		);

		$client = new GeminiClient( 'key', $mock_request, $mock_rate_limiter, $config );
		$client->get( 'Prompt' );

		$this->assertIsArray( $captured_body );
		$this->assertArrayHasKey( 'generationConfig', $captured_body );
		$this->assertSame( 'application/json', $captured_body['generationConfig']['response_mime_type'] );
		$this->assertSame( 0.2, $captured_body['generationConfig']['temperature'] );
	}
}


