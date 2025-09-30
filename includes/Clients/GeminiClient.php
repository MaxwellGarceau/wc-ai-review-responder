<?php
/**
 * AI client for interacting with the Gemini API.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Clients;

use WcAiReviewResponder\Exceptions\AiResponseFailure;
use WcAiReviewResponder\Exceptions\RateLimitExceededException;
use WcAiReviewResponder\Clients\Request;
use WcAiReviewResponder\RateLimiting\RateLimiter;

/**
 * Gemini client class that sends prompts to the Gemini API and returns raw responses.
 */
class GeminiClient implements AiClientInterface {
	/**
	 * Gemini API endpoint URL.
	 *
	 * We can leave this as one big URL for now because we only need to send one request.
	 *
	 * @var string
	 */
	private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

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
	 * Gemini API configuration options.
	 *
	 * @var array
	 */
	private $config;


	/**
	 * Constructor.
	 *
	 * @param string      $api_key     Gemini API key.
	 * @param Request     $request     Request handler instance.
	 * @param RateLimiter $rate_limiter Rate limiter instance.
	 * @param array       $config      Optional Gemini API configuration options.
	 */
	public function __construct( string $api_key, Request $request, RateLimiter $rate_limiter, array $config = array() ) {
		$this->api_key      = $api_key;
		$this->request      = $request;
		$this->rate_limiter = $rate_limiter;
		$this->config       = $this->merge_default_config( $config );
	}


	/**
	 * Get a reply from the AI provider using a prepared prompt.
	 *
	 * @param string $prompt Prepared prompt string.
	 * @return string Raw AI response.
	 * @throws AiResponseFailure When the API request fails.
	 */
	public function get( string $prompt ): string {
		if ( empty( $this->api_key ) ) {
			throw new AiResponseFailure( 'Missing Gemini API key.' );
		}

		// Check rate limits before making the request.
		$identifier = $this->get_rate_limit_identifier();
		$this->rate_limiter->check_rate_limit( $identifier );

		$response = $this->make_gemini_request( $prompt );

		if ( ! is_string( $response ) || '' === trim( $response ) ) {
			throw new AiResponseFailure( 'AI returned an empty response.', 0, null, array( 'prompt' => wp_kses_post( $prompt ) ) );
		}

		// Record successful request for rate limiting.
		$this->rate_limiter->record_request( $identifier );

		return $response;
	}

	/**
	 * Assemble the request body, make the request, and extract the response text.
	 *
	 * @param string $prompt The prompt to send to Gemini.
	 * @return string The generated response from Gemini.
	 * @throws AiResponseFailure When the API request fails.
	 */
	private function make_gemini_request( string $prompt ): string {
		$request_body = $this->build_request_body( $prompt );

		$data = $this->request->post( self::GEMINI_API_URL, $this->api_key, $request_body );

		return $this->extract_response_text( $data );
	}

	/**
	 * Build the request body for Gemini API.
	 *
	 * @param string $prompt The prompt to include in the request.
	 * @return array The formatted request body for Gemini API.
	 */
	private function build_request_body( string $prompt ): array {
		return array(
			'contents' => array(
				array(
					'parts' => array(
						array(
							'text' => $prompt,
						),
					),
				),
			),
			'generationConfig' => $this->config,
		);
	}

	/**
	 * Extract text content from Gemini API response.
	 *
	 * @param array $data The decoded JSON response from Gemini API.
	 * @return string The extracted text content.
	 * @throws AiResponseFailure When the response format is invalid.
	 */
	private function extract_response_text( array $data ): string {
		if ( ! isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
			throw new AiResponseFailure( 'Invalid response format from Gemini API' );
		}

		return $data['candidates'][0]['content']['parts'][0]['text'];
	}

	/**
	 * Get the identifier for rate limiting.
	 *
	 * Uses user ID for logged-in users, IP address for anonymous users.
	 *
	 * @return string Rate limiting identifier.
	 */
	private function get_rate_limit_identifier(): string {
		if ( is_user_logged_in() ) {
			return 'user_' . get_current_user_id();
		}

		// For anonymous users, use IP address.
		$ip = $this->get_client_ip();
		return 'ip_' . $ip;
	}

	/**
	 * Merge user-provided config with default configuration.
	 *
	 * @param array $user_config User-provided configuration options.
	 * @return array Merged configuration.
	 */
	private function merge_default_config( array $user_config ): array {
		$default_config = array(
			'response_mime_type' => 'text/plain',
		);

		return array_merge( $default_config, $user_config );
	}

	/**
	 * Get the client's IP address.
	 *
	 * @return string Client IP address.
	 */
	private function get_client_ip(): string {
		$ip_headers = array(
			'HTTP_CF_CONNECTING_IP',     // Cloudflare.
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $ip_headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
				// Handle comma-separated IPs (from proxies).
				$ip = explode( ',', $ip )[0];
				$ip = trim( $ip );

				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
					return $ip;
				}
			}
		}

		// Fallback to REMOTE_ADDR even if it's a private IP.
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	}
}
