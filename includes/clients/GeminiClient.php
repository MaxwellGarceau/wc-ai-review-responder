<?php
/**
 * AI client for interacting with the Gemini API.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Clients;

use WcAiReviewResponder\Exceptions\AiResponseFailure;
use WcAiReviewResponder\Clients\Request;

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
	 * Constructor.
	 *
	 * @param string  $api_key Gemini API key.
	 * @param Request $request Request handler instance.
	 */
	public function __construct( string $api_key, Request $request ) {
		$this->api_key = $api_key;
		$this->request = $request;
	}


	/**
	 * Get a reply from the AI provider using a prepared prompt.
	 *
	 * @param string $prompt Prepared prompt string.
	 * @return string Raw AI response.
	 * @throws AiResponseFailure When API key is missing or AI returns empty response.
	 */
	public function get( string $prompt ): string {
		if ( empty( $this->api_key ) ) {
			throw new AiResponseFailure( 'Missing Gemini API key.' );
		}

		$response = $this->make_gemini_request( $prompt );

		if ( ! is_string( $response ) || '' === trim( $response ) ) {
			throw new AiResponseFailure( 'AI returned an empty response.', 0, null, array( 'prompt' => wp_kses_post( $prompt ) ) );
		}

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
}
