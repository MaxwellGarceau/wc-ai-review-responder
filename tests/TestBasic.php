<?php
use PHPUnit\Framework\TestCase;

/**
 * Basic test to verify test discovery.
 */
final class TestBasic extends TestCase {

	/**
	 * Test basic functionality.
	 */
	public function testBasic(): void {
		$this->assertEquals( 1, 1 );
	}
}
