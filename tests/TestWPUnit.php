<?php
/**
 * Test to check if WP_UnitTestCase is available.
 */

class TestWPUnit extends WP_UnitTestCase {

	/**
	 * Test that WP_UnitTestCase works.
	 */
	public function test_wp_unit_test_case() {
		$this->assertTrue( true );
	}
}
