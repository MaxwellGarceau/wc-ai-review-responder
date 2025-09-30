<?php
/**
 * ReviewContext test cases.
 *
 * Added by assistant.
 *
 * @package WcAiReviewResponder
 */

/**
 * Test ReviewContext getters and formatting.
 */
class ReviewContextTest extends WP_UnitTestCase {

	public function test_accessors_and_formatting() {
		$ctx = new \WcAiReviewResponder\LLM\Prompts\ReviewContext( array(
			'rating' => 3,
			'comment' => 'ok',
			'product_name' => 'Name',
		) );

		$this->assertSame( 3, $ctx->get_rating() );
		$this->assertSame( 'ok', $ctx->get_comment() );
		$this->assertSame( 'Name', $ctx->get_product_name() );
		$this->assertSame( '3/5 stars', $ctx->get_formatted_rating() );
	}
}


