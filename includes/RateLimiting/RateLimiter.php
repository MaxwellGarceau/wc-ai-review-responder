<?php
/**
 * Rate limiter for controlling API request frequency.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\RateLimiting;

use WcAiReviewResponder\Exceptions\RateLimitExceededException;

/**
 * Rate limiter class for managing API request limits.
 *
 * Provides configurable rate limiting with WordPress hooks for customization.
 * Uses WordPress transients for efficient storage and automatic cleanup.
 */
class RateLimiter {

	/**
	 * Default requests per hour limit.
	 *
	 * @var int
	 */
	private const DEFAULT_REQUESTS_PER_HOUR = 100;

	/**
	 * Default requests per day limit.
	 *
	 * @var int
	 */
	private const DEFAULT_REQUESTS_PER_DAY = 1000;

	/**
	 * Transient key prefix for rate limiting data.
	 *
	 * @var string
	 */
	private const TRANSIENT_PREFIX = 'wc_ai_review_responder_rate_limit_';

	/**
	 * Check if a request is allowed under current rate limits.
	 *
	 * @param string $identifier Unique identifier for rate limiting (e.g., user ID, IP).
	 * @return void
	 * @throws RateLimitExceededException When rate limit is exceeded.
	 */
	public function check_rate_limit( string $identifier ): void {
		$hourly_limit = $this->get_hourly_limit();
		$daily_limit  = $this->get_daily_limit();

		$hourly_count = $this->get_request_count( $identifier, 'hour' );
		$daily_count  = $this->get_request_count( $identifier, 'day' );

		if ( $hourly_count >= $hourly_limit ) {
			$reset_time = $this->get_next_hour_timestamp();
			$exception  = new RateLimitExceededException(
				'Rate limit exceeded: too many requests per hour.',
				$reset_time
			);
			throw $exception;
		}

		if ( $daily_count >= $daily_limit ) {
			$reset_time = $this->get_next_day_timestamp();
			$exception  = new RateLimitExceededException(
				'Rate limit exceeded: too many requests per day.',
				$reset_time
			);
			throw $exception;
		}
	}

	/**
	 * Record a successful API request.
	 *
	 * @param string $identifier Unique identifier for rate limiting.
	 * @return void
	 */
	public function record_request( string $identifier ): void {
		$this->increment_request_count( $identifier, 'hour' );
		$this->increment_request_count( $identifier, 'day' );
	}

	/**
	 * Get the current hourly request limit.
	 *
	 * @return int Requests allowed per hour.
	 */
	private function get_hourly_limit(): int {
		/**
		 * Filter the hourly rate limit for AI requests.
		 *
		 * @since 1.0.0
		 *
		 * @param int $limit Requests allowed per hour. Default 100.
		 */
		return apply_filters( 'wc_ai_review_responder_hourly_rate_limit', self::DEFAULT_REQUESTS_PER_HOUR );
	}

	/**
	 * Get the current daily request limit.
	 *
	 * @return int Requests allowed per day.
	 */
	private function get_daily_limit(): int {
		/**
		 * Filter the daily rate limit for AI requests.
		 *
		 * @since 1.0.0
		 *
		 * @param int $limit Requests allowed per day. Default 1000.
		 */
		return apply_filters( 'wc_ai_review_responder_daily_rate_limit', self::DEFAULT_REQUESTS_PER_DAY );
	}

	/**
	 * Get the current request count for a time period.
	 *
	 * @param string $identifier Unique identifier.
	 * @param string $period Time period ('hour' or 'day').
	 * @return int Current request count.
	 */
	private function get_request_count( string $identifier, string $period ): int {
		$key  = $this->get_transient_key( $identifier, $period );
		$data = get_transient( $key );

		if ( false === $data ) {
			return 0;
		}

		// Check if the time window has expired.
		$current_time = time();
		if ( 'hour' === $period ) {
			$window_start = $this->get_current_hour_timestamp();
		} else {
			$window_start = $this->get_current_day_timestamp();
		}

		if ( $data['timestamp'] < $window_start ) {
			return 0;
		}

		return $data['count'];
	}

	/**
	 * Increment the request count for a time period.
	 *
	 * @param string $identifier Unique identifier.
	 * @param string $period Time period ('hour' or 'day').
	 * @return void
	 */
	private function increment_request_count( string $identifier, string $period ): void {
		$key  = $this->get_transient_key( $identifier, $period );
		$data = get_transient( $key );

		$current_time = time();
		if ( 'hour' === $period ) {
			$window_start = $this->get_current_hour_timestamp();
			$expiration   = 3600; // 1 hour.
		} else {
			$window_start = $this->get_current_day_timestamp();
			$expiration   = 86400; // 24 hours.
		}

		if ( false === $data || $data['timestamp'] < $window_start ) {
			// Start new time window.
			$data = array(
				'count'     => 1,
				'timestamp' => $current_time,
			);
		} else {
			// Increment existing count.
			++$data['count'];
		}

		set_transient( $key, $data, $expiration );
	}

	/**
	 * Get the transient key for rate limiting data.
	 *
	 * @param string $identifier Unique identifier.
	 * @param string $period Time period.
	 * @return string Transient key.
	 */
	private function get_transient_key( string $identifier, string $period ): string {
		return self::TRANSIENT_PREFIX . $period . '_' . md5( $identifier );
	}

	/**
	 * Get the current hour timestamp (start of hour).
	 *
	 * @return int Timestamp.
	 */
	private function get_current_hour_timestamp(): int {
		return strtotime( 'today ' . gmdate( 'H:00:00' ) );
	}

	/**
	 * Get the next hour timestamp.
	 *
	 * @return int Timestamp.
	 */
	private function get_next_hour_timestamp(): int {
		return $this->get_current_hour_timestamp() + 3600;
	}

	/**
	 * Get the current day timestamp (start of day).
	 *
	 * @return int Timestamp.
	 */
	private function get_current_day_timestamp(): int {
		return strtotime( 'today 00:00:00' );
	}

	/**
	 * Get the next day timestamp.
	 *
	 * @return int Timestamp.
	 */
	private function get_next_day_timestamp(): int {
		return $this->get_current_day_timestamp() + 86400;
	}
}
