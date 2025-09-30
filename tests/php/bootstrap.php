<?php
/**
 * Test bootstrap file.
 */

// Load PHPUnit Polyfills before WordPress test bootstrap
require_once dirname( dirname( __DIR__ ) ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

// Resolve WordPress test environment directory
// Prefer wp-env provided test suite inside the container
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
}
if ( ! $_tests_dir ) {
    // Fallback to Composer-installed wp-phpunit
    $_tests_dir = dirname( dirname( __DIR__ ) ) . '/vendor/wp-phpunit/wp-phpunit';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
    echo "Could not find WordPress test library in: $_tests_dir\n";
    echo "Ensure wp-phpunit is installed (composer require --dev wp-phpunit/wp-phpunit)\n";
    exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    // Load the main plugin file (which includes the autoloader)
    require dirname( dirname( dirname( __FILE__ ) ) ) . '/wc-ai-review-responder.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
