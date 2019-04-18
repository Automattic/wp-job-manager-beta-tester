<?php
/**
 * File containing the class \WP_Job_Manager_Beta_Tester\WP_Job_Manager_Beta_Tester.
 *
 * @package wp-job-manager-beta-tester
 * @since   1.0.0
 */

namespace WP_Job_Manager_Beta_Tester;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WP Job Manager Beta tester class.
 *
 * @class WP_Job_Manager_Beta_Tester
 */
final class WP_Job_Manager_Beta_Tester {
	const WP_JOB_MANAGER_BETA_TESTER_REPORT_ISSUE_URL = 'https://github.com/Automattic/WP-Job-Manager/issues/new';

	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Plugin directory.
	 *
	 * @var string
	 */
	public $plugin_dir;

	/**
	 * Plugin URL.
	 *
	 * @var string
	 */
	public $plugin_url;

	/**
	 * Initialize the singleton instance.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->plugin_dir = dirname( __DIR__ );
		$this->plugin_url = untrailingslashit( plugins_url( '', WP_JOB_MANAGER_BETA_TESTER_PLUGIN_BASENAME ) );
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		$instance = self::instance();

		$instance->include_dependencies();

		add_action( 'init', [ $instance, 'load_plugin_textdomain' ] );

		Admin::instance()->init();

		$channel = Admin::get_settings()->channel;
		Updater::instance()->init( $channel );
	}

	/**
	 * Include required files.
	 */
	private function include_dependencies() {
		include_once $this->plugin_dir . '/includes/class-admin.php';
		include_once $this->plugin_dir . '/includes/updater/class-abstract-updater.php';
		include_once $this->plugin_dir . '/includes/class-updater.php';
	}

	/**
	 * Loads textdomain for plugin.
	 */
	public function load_plugin_textdomain() {
		$domain = 'wp-job-manager-beta-tester';
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, $domain );

		unload_textdomain( $domain );
		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
