<?php
/**
 * GeminiClientFactory test cases.
 *
 * Verifies that factory constructs clients with provided dependencies and
 * forwards configuration options.
 *
 * @package WcAiReviewResponder
 */

use WcAiReviewResponder\Clients\GeminiClientFactory;

/**
 * Test the GeminiClientFactory class.
 */
class GeminiClientFactoryTest extends WP_UnitTestCase {

	/**
	 * Test that create() returns GeminiClient and config propagates.
	 */
	public function test_create_returns_client_with_config() {
		$factory = new GeminiClientFactory(
			'test-api-key',
			new \WcAiReviewResponder\Clients\Request(),
			new \WcAiReviewResponder\RateLimiting\RateLimiter()
		);

		$client = $factory->create( array( 'temperature' => 0.1 ) );
		$this->assertInstanceOf( \WcAiReviewResponder\Clients\GeminiClient::class, $client );

		// Use pre_http_request to intercept and assert that config is forwarded.
		$asserted = false;
		add_filter( 'pre_http_request', function( $preempt, $args, $url ) use ( &$asserted ) {
			$body = json_decode( $args['body'], true );
			if ( isset( $body['generationConfig']['temperature'] ) ) {
				$asserted = ( 0.1 === $body['generationConfig']['temperature'] );
				return array(
					'headers'  => array(),
					'body'     => wp_json_encode( array(
						'candidates' => array(
							array(
								'content' => array(
									'parts' => array(
										array( 'text' => 'ok' ),
									),
								),
							),
						),
					) ),
					'response' => array( 'code' => 200, 'message' => 'OK' ),
				);
			}
			return false;
		}, 10, 3 );

		$client->get( 'Prompt' );
		$this->assertTrue( $asserted, 'Factory-provided config should be forwarded to request body' );
	}
}


