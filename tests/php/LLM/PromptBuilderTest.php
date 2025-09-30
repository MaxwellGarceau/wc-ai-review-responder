<?php
/**
 * PromptBuilder test cases.
 *
 * Added by assistant.
 *
 * @package WcAiReviewResponder
 */

use WcAiReviewResponder\LLM\PromptBuilder;
use WcAiReviewResponder\LLM\Prompts\TemplateType;
use WcAiReviewResponder\LLM\Prompts\Moods\MoodsType;

/**
 * Test PromptBuilder.
 */
class PromptBuilderTest extends WP_UnitTestCase {

	public function test_build_prompt_includes_context_and_mood() {
		$builder = new PromptBuilder();
		$context = array( 'rating' => 5, 'comment' => 'Love it!', 'product_name' => 'Amazing Widget' );
		$prompt  = $builder->build_prompt( $context, TemplateType::DEFAULT, MoodsType::ENTHUSIASTIC_APPRECIATOR );

		$this->assertStringContainsString( 'Amazing Widget', $prompt );
		$this->assertStringContainsString( '5/5 stars', $prompt );
		$this->assertStringContainsString( 'Love it!', $prompt );
		$this->assertStringContainsString( 'Write with genuine enthusiasm and gratitude', $prompt );
		$this->assertStringContainsString( 'Your response:', $prompt );
	}
}


