<?php
/**
 * HTTP request handler for external API calls.
 *
 * @package WcAiReviewResponder
 * @since   1.0.0
 */

namespace WcAiReviewResponder\Clients;

use WcAiReviewResponder\Exceptions\AiResponseFailure;

/**
 * Request class that handles HTTP requests to external APIs.
 */
class Request {

	/**
	 * Make a POST request to an external API.
	 *
	 * @param string $url The API endpoint URL.
	 * @param string $api_key The API key for authentication.
	 * @param array  $request_body The request payload.
	 * @return array The decoded JSON response.
	 * @throws AiResponseFailure When the request fails or returns an error.
	 */
	public function post( string $url, string $api_key, array $request_body ): array {
		$args = array(
			'method'  => 'POST',
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => wp_json_encode( $request_body ),
			'timeout' => 30,
		);

		$url_with_key = add_query_arg( 'key', $api_key, $url );

		$response = wp_remote_request( $url_with_key, $args );

		if ( is_wp_error( $response ) ) {
			throw new AiResponseFailure( 'Failed to connect to API: ' . esc_html( $response->get_error_message() ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( 200 !== $response_code ) {
			$error_data    = json_decode( $response_body, true );
			$error_message = isset( $error_data['error']['message'] ) ? $error_data['error']['message'] : 'Unknown API error';
			throw new AiResponseFailure( 'API error: ' . esc_html( $error_message ) );
		}

		$data = json_decode( $response_body, true );

		if ( null === $data ) {
			throw new AiResponseFailure( 'Invalid JSON response from API' );
		}

		return $data;
	}
}
