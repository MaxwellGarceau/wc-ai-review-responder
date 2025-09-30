<?php
/**
 * LLM moods and templates test cases.
 *
 * Added by assistant.
 *
 * @package WcAiReviewResponder
 */

/**
 * Test moods via MoodFactory and templates via TemplateType classes
 * to ensure they generate sensible prompts.
 */
class MoodsAndTemplatesTest extends WP_UnitTestCase {

	public function test_all_moods_apply_and_preserve_base_prompt() {
		$base_prompt = 'Base prompt content';
		$context     = new \WcAiReviewResponder\LLM\Prompts\ReviewContext( array(
			'rating' => 4,
			'comment' => 'Good',
			'product_name' => 'Prod',
		) );

		$factory = new \WcAiReviewResponder\LLM\Prompts\Moods\MoodFactory();
		foreach ( \WcAiReviewResponder\LLM\Prompts\Moods\MoodsType::cases() as $moodType ) {
			$mood = $factory->get_mood_by_type( $moodType );
			$this->assertInstanceOf( \WcAiReviewResponder\LLM\Prompts\Moods\MoodInterface::class, $mood );
			$result = $mood->apply( $base_prompt, $context );
			$this->assertIsString( $result );
			$this->assertStringContainsString( $base_prompt, $result );
		}
	}

	public function test_all_templates_include_context_and_footer() {
		$context = new \WcAiReviewResponder\LLM\Prompts\ReviewContext( array(
			'rating' => 2,
			'comment' => 'Not great',
			'product_name' => 'Thing',
		) );

		$map = array(
			'default' => \WcAiReviewResponder\LLM\Prompts\Templates\DefaultTemplate::class,
			'enthusiastic_five_star' => \WcAiReviewResponder\LLM\Prompts\Templates\EnthusiasticFiveStarTemplate::class,
			'positive_with_critique' => \WcAiReviewResponder\LLM\Prompts\Templates\PositiveWithCritiqueTemplate::class,
			'product_misunderstanding' => \WcAiReviewResponder\LLM\Prompts\Templates\ProductMisunderstandingTemplate::class,
			'defective_product' => \WcAiReviewResponder\LLM\Prompts\Templates\DefectiveProductTemplate::class,
			'shipping_issue' => \WcAiReviewResponder\LLM\Prompts\Templates\ShippingIssueTemplate::class,
			'value_price_concern' => \WcAiReviewResponder\LLM\Prompts\Templates\ValuePriceConcernTemplate::class,
		);

		foreach ( $map as $key => $class ) {
			$template = new $class();
			$prompt   = $template->get_prompt( $context );
			$this->assertStringContainsString( 'Thing', $prompt, 'Template '.$key.' should include product name' );
			$this->assertStringContainsString( '2/5 stars', $prompt, 'Template '.$key.' should include formatted rating' );
			$this->assertStringContainsString( 'Not great', $prompt, 'Template '.$key.' should include comment' );
			$this->assertStringContainsString( 'Your response:', $prompt, 'Template '.$key.' should include output footer' );
		}
	}
}


