<?php
/**
 * Request HTTP behavior test cases.
 *
 * These tests validate error handling and JSON parsing paths for the Request
 * class which wraps wp_remote_request.
 *
 * @package WcAiReviewResponder
 */

use WcAiReviewResponder\Clients\Request;

/**
 * Test the Request class.
 */
class RequestTest extends WP_UnitTestCase {

	/**
	 * Test that non-200 responses throw AiResponseFailure with parsed error.
 	*/
	public function test_non_200_response_throws_exception() {
		$request = new Request();

		$mock_url  = 'https://example.test/endpoint';
		$api_key   = 'k';
		$payload   = array( 'x' => 1 );
		$error_obj = array( 'error' => array( 'message' => 'Bad stuff' ) );

		add_filter( 'pre_http_request', function( $preempt, $args, $url ) use ( $mock_url, $error_obj ) {
			if ( $url === add_query_arg( 'key', 'k', $mock_url ) ) {
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode( $error_obj ),
					'response' => array( 'code' => 500, 'message' => 'Internal Server Error' ),
				);
			}
			return false;
		}, 10, 3 );

		$this->expectException( \WcAiReviewResponder\Exceptions\AiResponseFailure::class );
		$request->post( $mock_url, $api_key, $payload );
	}

	/**
	 * Test that invalid JSON body throws AiResponseFailure.
	 */
	public function test_invalid_json_body_throws_exception() {
		$request = new Request();

		$mock_url = 'https://example.test/endpoint';
		$api_key  = 'k';
		$payload  = array( 'x' => 1 );

		add_filter( 'pre_http_request', function( $preempt, $args, $url ) use ( $mock_url ) {
			if ( $url === add_query_arg( 'key', 'k', $mock_url ) ) {
				return array(
					'headers'  => array(),
					'body'     => 'not-json',
					'response' => array( 'code' => 200, 'message' => 'OK' ),
				);
			}
			return false;
		}, 10, 3 );

		$this->expectException( \WcAiReviewResponder\Exceptions\AiResponseFailure::class );
		$request->post( $mock_url, $api_key, $payload );
	}

	/**
	 * Test that a 200 response with valid JSON returns decoded array.
	 */
	public function test_success_returns_decoded_array() {
		$request = new Request();

		$mock_url = 'https://example.test/endpoint';
		$api_key  = 'k';
		$payload  = array( 'x' => 1 );
		$data     = array( 'ok' => true );

		add_filter( 'pre_http_request', function( $preempt, $args, $url ) use ( $mock_url, $data ) {
			if ( $url === add_query_arg( 'key', 'k', $mock_url ) ) {
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode( $data ),
					'response' => array( 'code' => 200, 'message' => 'OK' ),
				);
			}
			return false;
		}, 10, 3 );

		$result = $request->post( $mock_url, $api_key, $payload );
		$this->assertIsArray( $result );
		$this->assertTrue( $result['ok'] );
	}
}


