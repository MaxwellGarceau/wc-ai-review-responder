<?php
/**
 * RateLimiter test cases.
 *
 * @package WcAiReviewResponder
 */

// Manually load required files
require_once dirname( dirname( __FILE__ ) ) . '/includes/rate-limiting/RateLimiter.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/exceptions/RateLimitExceededException.php';

/**
 * Test the RateLimiter class.
 */
class RateLimiterTest extends WP_UnitTestCase {

	/**
	 * Rate limiter instance.
	 *
	 * @var \WcAiReviewResponder\RateLimiting\RateLimiter
	 */
	private $rate_limiter;

	/**
	 * Set up test fixtures.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->rate_limiter = new \WcAiReviewResponder\RateLimiting\RateLimiter();
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
	 * Test that rate limiter allows requests within limits.
	 */
	public function test_allows_requests_within_limits() {
		$identifier = 'test_user_123';

		// Should not throw exception for first request.
		$this->rate_limiter->check_rate_limit( $identifier );
		$this->rate_limiter->record_request( $identifier );

		// Should not throw exception for second request.
		$this->rate_limiter->check_rate_limit( $identifier );
		$this->rate_limiter->record_request( $identifier );

		$this->assertTrue( true, 'Requests within limits should be allowed' );
	}

	/**
	 * Test that rate limiter blocks requests exceeding hourly limit.
	 */
	public function test_blocks_requests_exceeding_hourly_limit() {
		$identifier   = 'test_user_hourly';
		$hourly_limit = 2; // Set a low limit for testing.

		// Mock the hourly limit filter.
		add_filter( 'wc_ai_review_responder_hourly_rate_limit', function() use ( $hourly_limit ) {
			return $hourly_limit;
		} );

		// Make requests up to the limit.
		for ( $i = 0; $i < $hourly_limit; $i++ ) {
			$this->rate_limiter->check_rate_limit( $identifier );
			$this->rate_limiter->record_request( $identifier );
		}

		// Next request should be blocked.
		$this->expectException( \WcAiReviewResponder\Exceptions\RateLimitExceededException::class );
		$this->rate_limiter->check_rate_limit( $identifier );

		remove_filter( 'wc_ai_review_responder_hourly_rate_limit', '__return_false' );
	}

	/**
	 * Test that rate limiter blocks requests exceeding daily limit.
	 */
	public function test_blocks_requests_exceeding_daily_limit() {
		$identifier  = 'test_user_daily';
		$daily_limit = 3; // Set a low limit for testing.

		// Mock the daily limit filter.
		add_filter( 'wc_ai_review_responder_daily_rate_limit', function() use ( $daily_limit ) {
			return $daily_limit;
		} );

		// Make requests up to the limit.
		for ( $i = 0; $i < $daily_limit; $i++ ) {
			$this->rate_limiter->check_rate_limit( $identifier );
			$this->rate_limiter->record_request( $identifier );
		}

		// Next request should be blocked.
		$this->expectException( \WcAiReviewResponder\Exceptions\RateLimitExceededException::class );
		$this->rate_limiter->check_rate_limit( $identifier );

		remove_filter( 'wc_ai_review_responder_daily_rate_limit', '__return_false' );
	}

	/**
	 * Test that rate limit exception includes reset timestamp.
	 */
	public function test_rate_limit_exception_includes_reset_timestamp() {
		$identifier   = 'test_user_reset';
		$hourly_limit = 1;

		// Mock the hourly limit filter.
		add_filter( 'wc_ai_review_responder_hourly_rate_limit', function() use ( $hourly_limit ) {
			return $hourly_limit;
		} );

		// Make one request.
		$this->rate_limiter->check_rate_limit( $identifier );
		$this->rate_limiter->record_request( $identifier );

		// Next request should throw exception with reset timestamp.
		try {
			$this->rate_limiter->check_rate_limit( $identifier );
			$this->fail( 'Expected RateLimitExceededException was not thrown' );
		} catch ( \WcAiReviewResponder\Exceptions\RateLimitExceededException $e ) {
			$reset_timestamp = $e->get_reset_timestamp();
			$this->assertIsInt( $reset_timestamp );
			$this->assertGreaterThan( time(), $reset_timestamp );
		}

		remove_filter( 'wc_ai_review_responder_hourly_rate_limit', '__return_false' );
	}

	/**
	 * Test that different identifiers have separate rate limits.
	 */
	public function test_different_identifiers_have_separate_limits() {
		$identifier1  = 'user_1';
		$identifier2  = 'user_2';
		$hourly_limit = 1;

		// Mock the hourly limit filter.
		add_filter( 'wc_ai_review_responder_hourly_rate_limit', function() use ( $hourly_limit ) {
			return $hourly_limit;
		} );

		// User 1 makes a request.
		$this->rate_limiter->check_rate_limit( $identifier1 );
		$this->rate_limiter->record_request( $identifier1 );

		// User 2 should still be able to make a request.
		$this->rate_limiter->check_rate_limit( $identifier2 );
		$this->rate_limiter->record_request( $identifier2 );

		// User 1 should be blocked.
		$this->expectException( \WcAiReviewResponder\Exceptions\RateLimitExceededException::class );
		$this->rate_limiter->check_rate_limit( $identifier1 );

		remove_filter( 'wc_ai_review_responder_hourly_rate_limit', '__return_false' );
	}

	/**
	 * Test that rate limits reset after time window expires.
	 */
	public function test_rate_limits_reset_after_time_window() {
		$identifier   = 'test_user_reset_window';
		$hourly_limit = 1;

		// Mock the hourly limit filter.
		add_filter( 'wc_ai_review_responder_hourly_rate_limit', function() use ( $hourly_limit ) {
			return $hourly_limit;
		} );

		// Make a request.
		$this->rate_limiter->check_rate_limit( $identifier );
		$this->rate_limiter->record_request( $identifier );

		// Should be blocked.
		$this->expectException( \WcAiReviewResponder\Exceptions\RateLimitExceededException::class );
		$this->rate_limiter->check_rate_limit( $identifier );

		// Manually expire the transient to simulate time passing.
		$transient_key = 'wc_ai_review_responder_rate_limit_hour_' . md5( $identifier );
		delete_transient( $transient_key );

		// Should now be allowed again.
		$this->rate_limiter->check_rate_limit( $identifier );
		$this->rate_limiter->record_request( $identifier );

		remove_filter( 'wc_ai_review_responder_hourly_rate_limit', '__return_false' );
	}

	/**
	 * Test that WordPress filters can customize rate limits.
	 */
	public function test_wordpress_filters_customize_rate_limits() {
		$custom_hourly_limit = 1; // Set very low for testing.

		// Add custom filter.
		add_filter( 'wc_ai_review_responder_hourly_rate_limit', function() use ( $custom_hourly_limit ) {
			return $custom_hourly_limit;
		} );

		// Test that the limits are applied.
		$identifier = 'test_custom_limits';

		// Make one request (should be allowed).
		$this->rate_limiter->check_rate_limit( $identifier );
		$this->rate_limiter->record_request( $identifier );

		// Second request should be blocked.
		$this->expectException( \WcAiReviewResponder\Exceptions\RateLimitExceededException::class );
		$this->rate_limiter->check_rate_limit( $identifier );

		// Clean up filters.
		remove_all_filters( 'wc_ai_review_responder_hourly_rate_limit' );
	}

	/**
	 * Test that rate limiter handles edge cases gracefully.
	 */
	public function test_handles_edge_cases_gracefully() {
		$identifier = '';

		// Empty identifier should not cause errors.
		$this->rate_limiter->check_rate_limit( $identifier );
		$this->rate_limiter->record_request( $identifier );

		// Very long identifier should be handled.
		$long_identifier = str_repeat( 'a', 1000 );
		$this->rate_limiter->check_rate_limit( $long_identifier );
		$this->rate_limiter->record_request( $long_identifier );

		$this->assertTrue( true, 'Edge cases should be handled gracefully' );
	}
}


