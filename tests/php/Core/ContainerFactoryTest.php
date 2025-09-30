<?php
/**
 * ContainerFactory test cases.
 *
 * Added by assistant.
 *
 * @package WcAiReviewResponder
 */

use WcAiReviewResponder\Core\ContainerFactory;

/**
 * Test the ContainerFactory class.
 */
class ContainerFactoryTest extends WP_UnitTestCase {

	public function test_build_returns_container_and_resolves_services() {
		$factory   = new ContainerFactory();
		$container = $factory->build();
		$this->assertInstanceOf( \DI\Container::class, $container );

		$ajax = $container->get( \WcAiReviewResponder\Endpoints\AjaxHandler::class );
		$this->assertInstanceOf( \WcAiReviewResponder\Endpoints\AjaxHandler::class, $ajax );

		$cli = $container->get( \WcAiReviewResponder\CLI\AiReviewCli::class );
		$this->assertInstanceOf( \WcAiReviewResponder\CLI\AiReviewCli::class, $cli );
	}
}


