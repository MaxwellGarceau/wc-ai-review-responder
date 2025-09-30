<?php
/**
 * Validation classes test cases.
 *
 * Added by assistant.
 *
 * @package WcAiReviewResponder
 */

/**
 * Test AiInputSanitizer, ReviewValidator, and ValidateAiResponse.
 */
class ValidationTest extends WP_UnitTestCase {

	public function test_ai_input_sanitizer_sanitizes_and_redacts() {
		$sanitizer = new \WcAiReviewResponder\Validation\AiInputSanitizer();
		$ctx       = array(
			'rating'       => '5',
			'comment'      => 'Great! <b>Love</b> it. Contact me: john@example.com visit https://example.com [shortcode]\n',
			'product_name' => " My\nProduct ",
		);
		$clean = $sanitizer->sanitize( $ctx );

		$this->assertSame( 5, $clean['rating'] );
		$this->assertStringNotContainsString( '<b>', $clean['comment'] );
		$this->assertStringNotContainsString( 'example.com', $clean['comment'] );
		$this->assertStringContainsString( '[redacted-email]', $clean['comment'] );
		$this->assertStringContainsString( '[redacted-url]', $clean['comment'] );
		$this->assertSame( 'My Product', $clean['product_name'] );
	}

	public function test_review_validator_rules() {
		$validator = new \WcAiReviewResponder\Validation\ReviewValidator();

		// Valid data should not throw.
		$validator->validate_for_ai_processing( array(
			'comment' => 'Ok',
			'rating'  => 4,
			'comment_id' => 1,
			'product_id' => 1,
			'product_name' => 'P',
			'product_description' => '',
			'author' => 'A',
		) );

		// Missing comment.
		$this->expectException( \WcAiReviewResponder\Exceptions\InvalidReviewException::class );
		$validator->validate_for_ai_processing( array( 'comment' => ' ', 'rating' => 5 ) );
	}

	public function test_validate_ai_response_sanitizes_output() {
		$validator = new \WcAiReviewResponder\Validation\ValidateAiResponse();
		$raw       = 'Hello <strong>world</strong><script>alert(1)</script>';
		$clean     = $validator->validate( $raw );
		$this->assertStringContainsString( 'Hello <strong>world</strong>', $clean );
		$this->assertStringNotContainsString( '<script>', $clean );
	}
}


