<?php
/**
 * Simple test to verify WordPress testing environment.
 */

class TestSimple extends WP_UnitTestCase {

	/**
	 * Test that WordPress is loaded.
	 */
	public function test_wordpress_loaded() {
		$this->assertTrue( function_exists( 'wp_get_current_user' ) );
	}

	/**
	 * Test that our plugin is loaded.
	 */
	public function test_plugin_loaded() {
		// Debug: Check if the main plugin file is loaded
		$this->assertTrue( defined( 'MAIN_PLUGIN_FILE' ), 'Main plugin file should be loaded' );
		
		// Manually load the RateLimiter file to test
		require_once dirname( dirname( __FILE__ ) ) . '/includes/rate-limiting/RateLimiter.php';
		
		// Debug: Check if class exists
		$this->assertTrue( class_exists( 'WcAiReviewResponder\RateLimiting\RateLimiter' ), 'RateLimiter class should exist' );
	}

	/**
	 * Test basic WordPress functionality.
	 */
	public function test_wordpress_functionality() {
		$user_id = wp_create_user( 'testuser', 'password', 'test@example.com' );
		$this->assertGreaterThan( 0, $user_id );
		
		$user = get_user_by( 'id', $user_id );
		$this->assertEquals( 'testuser', $user->user_login );
	}
}
