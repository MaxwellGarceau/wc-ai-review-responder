<?php
/**
 * SentimentAnalysis prompt test cases.
 *
 * Added by assistant.
 *
 * @package WcAiReviewResponder
 */

/**
 * Test SentimentAnalysis prompt includes required sections and JSON hint.
 */
class SentimentAnalysisTest extends WP_UnitTestCase {

	public function test_build_prompt_includes_expected_sections() {
		$builder = new \WcAiReviewResponder\LLM\Prompts\SentimentAnalysis();
		$context = array( 'rating' => 3, 'comment' => 'Mixed feelings', 'product_name' => 'Thing' );
		$prompt  = $builder->build_prompt( $context );

		$this->assertStringContainsString( 'The available moods are:', $prompt );
		$this->assertStringContainsString( 'The available templates are:', $prompt );
		$this->assertStringContainsString( 'Rating: 3/5 stars', $prompt );
		$this->assertStringContainsString( 'Product: Thing', $prompt );
		$this->assertStringContainsString( 'Comment: Mixed feelings', $prompt );
		$this->assertStringContainsString( 'Your response must be in JSON format', $prompt );
	}
}


