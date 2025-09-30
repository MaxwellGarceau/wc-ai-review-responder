<?php
/**
 * Factory for creating GeminiClient instances.
 *
 * @package WcAiReviewResponder
 * @since 1.1.0
 */

namespace WcAiReviewResponder\Clients;

use WcAiReviewResponder\Clients\Request;
use WcAiReviewResponder\RateLimiting\RateLimiter;

/**
 * Factory for creating configured instances of GeminiClient.
 */
class GeminiClientFactory {

	/**
	 * Gemini API key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Request handler instance.
	 *
	 * @var Request
	 */
	private $request;

	/**
	 * Rate limiter instance.
	 *
	 * @var RateLimiter
	 */
	private $rate_limiter;

	/**
	 * Constructor.
	 *
	 * @param string      $api_key     Gemini API key.
	 * @param Request     $request     Request handler instance.
	 * @param RateLimiter $rate_limiter Rate limiter instance.
	 */
	public function __construct( string $api_key, Request $request, RateLimiter $rate_limiter ) {
		$this->api_key      = $api_key;
		$this->request      = $request;
		$this->rate_limiter = $rate_limiter;
	}

	/**
	 * Creates a new instance of the GeminiClient with a specific configuration.
	 *
	 * @param array $config Optional configuration for the Gemini API.
	 * @return GeminiClient A new instance of the Gemini client.
	 */
	public function create( array $config = [] ): GeminiClient {
		return new GeminiClient( $this->api_key, $this->request, $this->rate_limiter, $config );
	}
}
