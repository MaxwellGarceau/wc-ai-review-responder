<?php
/**
 * ReviewModel test cases.
 *
 * Added by assistant.
 *
 * @package WcAiReviewResponder
 */

use WcAiReviewResponder\Models\ReviewModel;

/**
 * Test the ReviewModel class.
 */
class ReviewModelTest extends WP_UnitTestCase {

	/**
	 * Create a simple product and a review.
	 *
	 * @return array{product_id:int,comment_id:int}
	 */
	private function create_product_and_review(): array {
		$this->assertTrue( class_exists( '\\WC_Product_Simple' ), 'WooCommerce is not loaded: WC_Product_Simple missing.' );
		$product = new \WC_Product_Simple();
		$product->set_name( 'Test Product' );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_regular_price( '10' );
		$product->set_price( '10' );
		$product->save();

		$product_id = (int) $product->get_id();

		$comment_id = $this->factory->comment->create( array(
			'comment_post_ID' => $product_id,
			'comment_type'    => 'review',
			'comment_content' => 'Nice product',
		) );
		update_comment_meta( $comment_id, 'rating', 5 );

		return array( 'product_id' => $product_id, 'comment_id' => (int) $comment_id );
	}

	public function test_get_by_id_returns_expected_shape() {
		$ids   = $this->create_product_and_review();
		$model = new ReviewModel();
		$data  = $model->get_by_id( $ids['comment_id'] );

		$this->assertSame( $ids['comment_id'], $data['comment_id'] );
		$this->assertSame( $ids['product_id'], $data['product_id'] );
		$this->assertSame( 'Test Product', $data['product_name'] );
		$this->assertSame( 5, $data['rating'] );
		$this->assertSame( 'Nice product', $data['comment'] );
		$this->assertArrayHasKey( 'product_description', $data );
		$this->assertArrayHasKey( 'author', $data );
	}

	public function test_get_by_id_throws_for_non_review() {
		$post_id    = $this->factory->post->create();
		$comment_id = $this->factory->comment->create( array( 'comment_post_ID' => $post_id ) );
		$model      = new ReviewModel();
		$this->expectException( \WcAiReviewResponder\Exceptions\InvalidReviewException::class );
		$model->get_by_id( (int) $comment_id );
	}
}


