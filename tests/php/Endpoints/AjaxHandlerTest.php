<?php
/**
 * AjaxHandler test cases.
 *
 * These tests cover success and error flows for both generate and
 * get_ai_suggestions handlers, including permission and nonce checks.
 *
 * Added by assistant.
 *
 * @package WcAiReviewResponder
 */

use WcAiReviewResponder\Endpoints\AjaxHandler;

// Define a lightweight exception used to short-circuit wp_die during AJAX tests.
if ( ! class_exists( 'WcAiReviewResponder_TestAjaxDie' ) ) {
    class WcAiReviewResponder_TestAjaxDie extends \RuntimeException {}
}

/**
 * Test the AjaxHandler class.
 */
class AjaxHandlerTest extends WP_UnitTestCase {

	/**
	 * Create a mock AjaxHandler with dependency stubs.
	 *
	 * @return array{handler:AjaxHandler,mocks:array}
	 */
	private function create_handler_with_mocks() {
		$model            = $this->createMock( \WcAiReviewResponder\Models\ModelInterface::class );
		$prompt_builder   = $this->createMock( \WcAiReviewResponder\LLM\BuildPromptInterface::class );
		$factory          = $this->createMock( \WcAiReviewResponder\Clients\GeminiClientFactory::class );
		$response_validator = $this->createMock( \WcAiReviewResponder\Validation\ValidateAiResponseInterface::class );
		$sanitizer        = $this->createMock( \WcAiReviewResponder\Validation\AiInputSanitizer::class );
		$review_validator = $this->createMock( \WcAiReviewResponder\Validation\ReviewValidator::class );

		$handler = new AjaxHandler( $model, $prompt_builder, $factory, $response_validator, $sanitizer, $review_validator );

		return array(
			'handler' => $handler,
			'mocks'   => compact( 'model', 'prompt_builder', 'factory', 'response_validator', 'sanitizer', 'review_validator' ),
		);
	}

	/**
	 * Intercept wp_die JSON responses to capture output.
	 */
	private function set_json_die_handler() {
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}
		add_filter( 'wp_die_ajax_handler', function() {
			return function( $message, $title = '', $args = array() ) {
				echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON output from wp_send_json_*.
				throw new \WcAiReviewResponder_TestAjaxDie( 'wp_die' );
			};
		} );
		add_filter( 'wp_die_handler', function() {
			return function( $message, $title = '', $args = array() ) {
				echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON output from wp_send_json_*.
				throw new \WcAiReviewResponder_TestAjaxDie( 'wp_die' );
			};
		} );
	}

	/**
	 * Test handle_generate returns success JSON for valid request.
	 *
	 * Added by assistant.
	 */
	public function test_handle_generate_success() {
		
		$setup            = $this->create_handler_with_mocks();
		$handler          = $setup['handler'];
		$m                = $setup['mocks'];

		// Create admin user to pass capability check.
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		// Mock dependencies.
		$m['model']->method( 'get_by_id' )->willReturn( array( 'rating' => 5, 'comment' => 'Great', 'product_name' => 'Widget' ) );
		$m['sanitizer']->method( 'sanitize' )->willReturn( array( 'rating' => 5, 'comment' => 'Great', 'product_name' => 'Widget' ) );
		$m['review_validator']->expects( $this->once() )->method( 'validate_for_ai_processing' );
		$m['prompt_builder']->method( 'build_prompt' )->willReturn( 'PROMPT' );

		$client = $this->createMock( \WcAiReviewResponder\Clients\GeminiClient::class );
		$client->method( 'get' )->willReturn( 'RAW_REPLY' );
		$m['factory']->method( 'create' )->willReturn( $client );
		$m['response_validator']->method( 'validate' )->willReturn( 'CLEAN_REPLY' );

		// Prepare POST and nonce.
		$_POST['_wpnonce']  = wp_create_nonce( 'generate_ai_response' );
		$_POST['comment_id'] = 123;
		$_POST['template']   = 'default';
		$_POST['mood']       = 'enthusiastic_appreciator';

		$this->set_json_die_handler();
		ob_start();
		try {
			$handler->handle_generate();
		} catch ( \WcAiReviewResponder_TestAjaxDie $e ) {
			// Expected; prevents early process exit.
		}
		$json = ob_get_clean();
		$data = json_decode( $json, true );

		$this->assertIsArray( $data );
		$this->assertTrue( $data['success'] );
		$this->assertSame( 'CLEAN_REPLY', $data['data']['reply'] );
	}

	/**
	 * Test handle_generate fails for insufficient permissions.
	 *
	 * Added by assistant.
	 */
	public function test_handle_generate_insufficient_permissions() {
		
		$setup   = $this->create_handler_with_mocks();
		$handler = $setup['handler'];

		// User without moderate_comments capability.
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$_POST['_wpnonce']  = wp_create_nonce( 'generate_ai_response' );
		$_POST['comment_id'] = 1;

		$this->set_json_die_handler();
		ob_start();
		try {
			$handler->handle_generate();
		} catch ( \WcAiReviewResponder_TestAjaxDie $e ) {
			// Expected.
		}
		$json = ob_get_clean();
		$data = json_decode( $json, true );

		$this->assertFalse( $data['success'] );
		$this->assertSame( 'unauthorized', $data['data']['error_type'] );
	}

	/**
	 * Test handle_generate fails for invalid nonce.
	 *
	 * Added by assistant.
	 */
	public function test_handle_generate_invalid_nonce() {
		
		$setup   = $this->create_handler_with_mocks();
		$handler = $setup['handler'];

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$_POST['_wpnonce']  = 'bad';
		$_POST['comment_id'] = 1;

		$this->set_json_die_handler();
		ob_start();
		try {
			$handler->handle_generate();
		} catch ( \WcAiReviewResponder_TestAjaxDie $e ) {
			// Expected.
		}
		$json = ob_get_clean();
		$data = json_decode( $json, true );

		$this->assertFalse( $data['success'] );
		$this->assertSame( 'invalid_nonce', $data['data']['error_type'] );
	}

	/**
	 * Test handle_get_ai_suggestions returns JSON with mood and template.
	 *
	 * Added by assistant.
	 */
	public function test_handle_get_ai_suggestions_success() {
		
		$setup            = $this->create_handler_with_mocks();
		$handler          = $setup['handler'];
		$m                = $setup['mocks'];

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$m['model']->method( 'get_by_id' )->willReturn( array( 'rating' => 4, 'comment' => 'Ok', 'product_name' => 'Gadget' ) );
		$m['sanitizer']->method( 'sanitize' )->willReturn( array( 'rating' => 4, 'comment' => 'Ok', 'product_name' => 'Gadget' ) );
		$m['review_validator']->expects( $this->once() )->method( 'validate_for_ai_processing' );
		// Factory returns a client that returns JSON suggestions.
		$client = $this->createMock( \WcAiReviewResponder\Clients\GeminiClient::class );
		$client->method( 'get' )->willReturn( '{"mood":"professional_educator","template":"product_misunderstanding"}' );
		$m['factory']->method( 'create' )->willReturn( $client );

		$_POST['_wpnonce']  = wp_create_nonce( 'get_ai_suggestions' );
		$_POST['comment_id'] = 456;

		$this->set_json_die_handler();
		ob_start();
		try {
			$handler->handle_get_ai_suggestions();
		} catch ( \WcAiReviewResponder_TestAjaxDie $e ) {
			// Expected.
		}
		$json = ob_get_clean();
		$data = json_decode( $json, true );

		$this->assertTrue( $data['success'] );
		$this->assertSame( 'professional_educator', $data['data']['mood'] );
		$this->assertSame( 'product_misunderstanding', $data['data']['template'] );
	}
}


