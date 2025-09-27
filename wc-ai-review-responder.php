<?php
/**
 * Plugin Name: WC AI Review Responder
 * Version: 0.1.0
 * Author: The WordPress Contributors
 * Author URI: https://woo.com
 * Text Domain: wc-ai-review-responder
 * Domain Path: /languages
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package extension
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'MAIN_PLUGIN_FILE' ) ) {
	define( 'MAIN_PLUGIN_FILE', __FILE__ );
}

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload_packages.php';

use WcAiReviewResponder\Admin\Setup;
use WcAiReviewResponder\Ajax_Handler;

// phpcs:disable WordPress.Files.FileName

/**
 * WooCommerce fallback notice.
 *
 * @since 0.1.0
 */
function wc_ai_review_responder_missing_wc_notice() {
	/* translators: %s WC download URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Wc Ai Review Responder requires WooCommerce to be installed and active. You can download %s here.', 'wc_ai_review_responder' ), '<a href="https://woo.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

register_activation_hook( __FILE__, 'wc_ai_review_responder_activate' );

/**
 * Activation hook.
 *
 * @since 0.1.0
 */
function wc_ai_review_responder_activate() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'wc_ai_review_responder_missing_wc_notice' );
		return;
	}
}

if ( ! class_exists( 'wc_ai_review_responder' ) ) :
	/**
	 * The wc_ai_review_responder class.
	 */
	class wc_ai_review_responder {
		/**
		 * This class instance.
		 *
		 * @var \wc_ai_review_responder single instance of this class.
		 */
		private static $instance;

		/**
		 * Constructor.
		 */
		public function __construct() {
			if ( is_admin() ) {
				new Setup();
				$ajax = new Ajax_Handler();
				$ajax->register();
			}
		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'wc_ai_review_responder' ), $this->version );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'wc_ai_review_responder' ), $this->version );
		}

		/**
		 * Gets the main instance.
		 *
		 * Ensures only one instance can be loaded.
		 *
		 * @return \wc_ai_review_responder
		 */
		public static function instance() {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
endif;

add_action( 'plugins_loaded', 'wc_ai_review_responder_init', 10 );

/**
 * Initialize the plugin.
 *
 * @since 0.1.0
 */
function wc_ai_review_responder_init() {
	load_plugin_textdomain( 'wc_ai_review_responder', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	// Load environment variables from .env if available.
	if ( class_exists( '\\Dotenv\\Dotenv' ) ) {
		$dotenv = \Dotenv\Dotenv::createImmutable( dirname( __FILE__ ) );
		$dotenv->safeLoad();
	}

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'wc_ai_review_responder_missing_wc_notice' );
		return;
	}

	wc_ai_review_responder::instance();

}
