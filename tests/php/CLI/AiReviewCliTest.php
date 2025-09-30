<?php
/**
 * Test the AiReviewCli class.
 *
 * @package WcAiReviewResponder
 */

use WcAiReviewResponder\CLI\AiReviewCli;
use WcAiReviewResponder\Models\ReviewModel;
use WcAiReviewResponder\LLM\PromptBuilder;
use WcAiReviewResponder\Clients\GeminiClientFactory;
use WcAiReviewResponder\Validation\ValidateAiResponse;
use WcAiReviewResponder\Validation\ReviewValidator;
use WcAiReviewResponder\Validation\AiInputSanitizer;
use WcAiReviewResponder\Clients\GeminiClient;

// Provide a minimal WP_CLI polyfill so the command can run in tests.
if ( ! class_exists( '\\WP_CLI' ) ) {
    class WP_CLI {
        public static function log( $message ) {}
        public static function success( $message ) {}
        public static function error( $message ) { throw new \RuntimeException( $message ); }
    }
}

/**
 * Test the AiReviewCli class.
 */
class AiReviewCliTest extends WP_UnitTestCase {
	
	/** @var \WcAiReviewResponder\Models\ReviewModel&PHPUnit\Framework\MockObject\MockObject $review_handler */
	private $review_handler;
	/** @var \WcAiReviewResponder\LLM\PromptBuilder&PHPUnit\Framework\MockObject\MockObject $prompt_builder */
	private $prompt_builder;
	/** @var \WcAiReviewResponder\Clients\GeminiClientFactory&PHPUnit\Framework\MockObject\MockObject $ai_client_factory */
	private $ai_client_factory;
	/** @var \WcAiReviewResponder\Validation\ValidateAiResponse&PHPUnit\Framework\MockObject\MockObject $response_validator */
	private $response_validator;
	/** @var \WcAiReviewResponder\Validation\ReviewValidator&PHPUnit\Framework\MockObject\MockObject $review_validator */
	private $review_validator;
	/** @var \WcAiReviewResponder\Validation\AiInputSanitizer&PHPUnit\Framework\MockObject\MockObject $input_sanitizer */
	private $input_sanitizer;
	private $cli;

	public function setUp(): void {
		parent::setUp();

		$this->review_handler     = $this->createMock( ReviewModel::class );
		$this->prompt_builder     = $this->createMock( PromptBuilder::class );
		$this->ai_client_factory  = $this->createMock( GeminiClientFactory::class );
		$this->response_validator = $this->createMock( ValidateAiResponse::class );
		$this->review_validator   = $this->createMock( ReviewValidator::class );
		$this->input_sanitizer    = $this->createMock( AiInputSanitizer::class );

		$this->cli = new AiReviewCli(
			$this->review_handler,
			$this->prompt_builder,
			$this->ai_client_factory,
			$this->response_validator,
			$this->review_validator,
			$this->input_sanitizer
		);
	}

	/**
	 * Minimal constructor smoke test to avoid warnings and ensure DI wiring works.
	 *
	 * Added by assistant.
	 */
	public function test_constructor_initializes_cli() {
		$this->assertInstanceOf( AiReviewCli::class, $this->cli );
	}

	/**
	 * Test that the `test` command runs without errors.
	 */
	public function test_test_command_runs_successfully() {
		// Mock the AI clients returned by the factory in sequence:
		// 1) suggestion client, 2) generation client.
		/** @var \WcAiReviewResponder\Clients\GeminiClient&PHPUnit\Framework\MockObject\MockObject $suggestion_client */
		$suggestion_client = $this->createMock( GeminiClient::class );
		/** @var \WcAiReviewResponder\Clients\GeminiClient&PHPUnit\Framework\MockObject\MockObject $generate_client */
		$generate_client   = $this->createMock( GeminiClient::class );

		// Configure mocks.
		$this->review_handler->method( 'get_by_id' )->willReturn( array( 'comment' => 'test', 'rating' => 5 ) );
		$this->input_sanitizer->method( 'sanitize' )->willReturn( array( 'comment' => 'test', 'rating' => 5 ) );
		$this->ai_client_factory->method( 'create' )
			->willReturnOnConsecutiveCalls( $suggestion_client, $generate_client );
		$suggestion_client->method( 'get' )
			->willReturn( '{"mood": "enthusiastic_appreciator", "template": "default"}' );
		$generate_client->method( 'get' )
			->willReturn( 'RAW_FINAL_REPLY' );
		$this->response_validator->method( 'validate' )->willReturn( 'A valid response' );

		// Use a dummy comment ID.
		$comment_id = 123;

		// The test will fail if any exception is thrown.
		$this->cli->test( array( $comment_id ), array() );

		// Check if success was called.
		// We can't directly test WP_CLI::success, so we assume no exceptions means success.
		$this->assertTrue( true );
	}
}
