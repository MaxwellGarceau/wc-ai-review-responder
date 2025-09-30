<?php
/**
 * GeminiClient rate limiting integration test cases.
 *
 * @package WcAiReviewResponder
 */

/**
 * Test the GeminiClient rate limiting integration.
 */
class GeminiClientRateLimitingTest extends WP_UnitTestCase {

	/**
	 * Gemini client instance.
	 *
	 * @var \WcAiReviewResponder\Clients\GeminiClient
	 */
	private $gemini_client;

	/**
	 * Rate limiter instance.
	 *
	 * @var \WcAiReviewResponder\RateLimiting\RateLimiter
	 */
	private $rate_limiter;

	/**
	 * Request handler instance.
	 *
	 * @var \WcAiReviewResponder\Clients\Request
	 */
	private $request_handler;

	/**
	 * Set up test fixtures.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->rate_limiter   = new \WcAiReviewResponder\RateLimiting\RateLimiter();
		$this->request_handler = new \WcAiReviewResponder\Clients\Request();
		$this->gemini_client  = new \WcAiReviewResponder\Clients\GeminiClient(
			'test-api-key',
			$this->request_handler,
			$this->rate_limiter
		);
	}

	/**
	 * Clean up after tests.
	 */
	public function tearDown(): void {
		parent::tearDown();
		// Clean up any transients created during tests.
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wc_ai_review_responder_rate_limit_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wc_ai_review_responder_rate_limit_%'" );
	}

	/**
	 * Test that GeminiClient respects rate limits.
	 */
	public function test_gemini_client_respects_rate_limits() {
		// Mock the rate limiter to throw exception.
		$mock_rate_limiter = $this->createMock( \WcAiReviewResponder\RateLimiting\RateLimiter::class );
		$mock_rate_limiter->expects( $this->once() )
			->method( 'check_rate_limit' )
			->willThrowException( new \WcAiReviewResponder\Exceptions\RateLimitExceededException( 'Rate limit exceeded', time() + 3600 ) );

		$client = new \WcAiReviewResponder\Clients\GeminiClient(
			'test-api-key',
			$this->request_handler,
			$mock_rate_limiter
		);

		// Should throw rate limit exception before making API call.
		$this->expectException( \WcAiReviewResponder\Exceptions\RateLimitExceededException::class );
		$client->get( 'Test prompt' );
	}

	/**
	 * Test that GeminiClient records successful requests.
	 */
	public function test_gemini_client_records_successful_requests() {
		// Mock the rate limiter to track calls.
		$mock_rate_limiter = $this->createMock( \WcAiReviewResponder\RateLimiting\RateLimiter::class );
		$mock_rate_limiter->expects( $this->once() )
			->method( 'check_rate_limit' );
		$mock_rate_limiter->expects( $this->once() )
			->method( 'record_request' );

		// Mock the request handler to return a successful response.
		$mock_request = $this->createMock( \WcAiReviewResponder\Clients\Request::class );
		$mock_request->expects( $this->once() )
			->method( 'post' )
			->willReturn( array(
				'candidates' => array(
					array(
						'content' => array(
							'parts' => array(
								array( 'text' => 'Test response' )
							)
						)
					)
				)
			) );

		$client = new \WcAiReviewResponder\Clients\GeminiClient(
			'test-api-key',
			$mock_request,
			$mock_rate_limiter
		);

		$response = $client->get( 'Test prompt' );
		$this->assertEquals( 'Test response', $response );
	}

	/**
	 * Test that GeminiClient generates correct rate limit identifiers.
	 */
	public function test_gemini_client_generates_correct_identifiers() {
		// Test with logged-in user.
		wp_set_current_user( 1 );

		$mock_rate_limiter = $this->createMock( \WcAiReviewResponder\RateLimiting\RateLimiter::class );
		$mock_rate_limiter->expects( $this->once() )
			->method( 'check_rate_limit' )
			->with( $this->stringStartsWith( 'user_' ) );

		$client = new \WcAiReviewResponder\Clients\GeminiClient(
			'test-api-key',
			$this->request_handler,
			$mock_rate_limiter
		);

		// This will fail due to missing API key, but we're testing the identifier generation.
		$this->expectException( \WcAiReviewResponder\Exceptions\AiResponseFailure::class );
		$client->get( 'Test prompt' );
	}

	/**
	 * Test that GeminiClient handles anonymous users correctly.
	 */
	public function test_gemini_client_handles_anonymous_users() {
		// Ensure no user is logged in.
		wp_set_current_user( 0 );

		$mock_rate_limiter = $this->createMock( \WcAiReviewResponder\RateLimiting\RateLimiter::class );
		$mock_rate_limiter->expects( $this->once() )
			->method( 'check_rate_limit' )
			->with( $this->stringStartsWith( 'ip_' ) );

		$client = new \WcAiReviewResponder\Clients\GeminiClient(
			'test-api-key',
			$this->request_handler,
			$mock_rate_limiter
		);

		// This will fail due to missing API key, but we're testing the identifier generation.
		$this->expectException( \WcAiReviewResponder\Exceptions\AiResponseFailure::class );
		$client->get( 'Test prompt' );
	}

	/**
	 * Test that rate limiting works end-to-end with real rate limiter.
	 */
	public function test_end_to_end_rate_limiting() {
		$hourly_limit = 1;

		// Set a very low rate limit for testing.
		add_filter( 'wc_ai_review_responder_hourly_rate_limit', function() use ( $hourly_limit ) {
			return $hourly_limit;
		} );

		// First request should work (will fail due to API key, but rate limiting should pass).
		$this->expectException( \WcAiReviewResponder\Exceptions\AiResponseFailure::class );
		$this->gemini_client->get( 'Test prompt' );

		// Second request should be rate limited.
		$this->expectException( \WcAiReviewResponder\Exceptions\RateLimitExceededException::class );
		$this->gemini_client->get( 'Test prompt' );

		remove_filter( 'wc_ai_review_responder_hourly_rate_limit', '__return_false' );
	}
}


