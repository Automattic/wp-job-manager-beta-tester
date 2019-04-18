<?php
/**
 * Plugin Name: WP Job Manager Beta Tester
 * Plugin URI: https://wpjobmanager.com/
 * Description: Help us test upcoming versions of WP Job Manager. Warning: Do not use on production sites!
 * Version: 1.0.0-dev
 * Tested up to: 5.0
 * Requires PHP: 5.6
 * Author: Automattic
 * Author URI: https://wpjobmanager.com/
 * Text Domain: wp-job-manager-beta-tester
 * Domain Path: /languages/
 *
 * @package wp-job-manager-beta-tester
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_JOB_MANAGER_BETA_TESTER_VERSION', '1.0.0-dev' );
define( 'WP_JOB_MANAGER_BETA_TESTER_PLUGIN_FILE', __FILE__ );
define( 'WP_JOB_MANAGER_BETA_TESTER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( version_compare( phpversion(), '5.6.0', '<' ) ) {
	// translators: %1$s placeholder is minimum PHP version; %2$s is the version of PHP they have installed.
	die( esc_html( sprintf( __( 'WP Job Manager Beta Tester requires a minimum PHP version of %1$s, but you are running %2$s.', 'wp-job-manager-beta-tester' ), '5.6.0', phpversion() ) ) );
}

// Include deprecated functions.
require_once dirname( __FILE__ ) . '/includes/class-wp-job-manager-beta-tester.php';

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', array( 'WP_Job_Manager_Beta_Tester\WP_Job_Manager_Beta_Tester', 'init' ), 5 );

WP_Job_Manager_Beta_Tester\WP_Job_Manager_Beta_Tester::instance();

