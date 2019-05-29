<?php
/**
 * File containing the class \WP_Job_Manager_Beta_Tester\Updater.
 *
 * @package wp-job-manager-beta-tester
 * @since   1.0.0
 */

namespace WP_Job_Manager_Beta_Tester;

use WP_Job_Manager_Beta_Tester\Updater\Plugin_Package;
use WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater;
use WP_Job_Manager_Beta_Tester\Updater\Sources\Github;
use WP_Job_Manager_Beta_Tester\Updater\Sources\Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing the update logic specific to this plugin.
 *
 * @class \WP_Job_Manager_Beta_Tester\Updater
 */
final class Updater extends Abstract_Updater {
	/**
	 * Gets the source object to fetch the version plugin packages.
	 *
	 * @return Source
	 */
	public function get_plugin_package_source() {
		return new Github( $this->get_plugin_slug(), 'Automattic/WP-Job-Manager' );
	}

	/**
	 * Gets the plugin slug.
	 *
	 * @return string
	 */
	public function get_plugin_slug() {
		return 'wp-job-manager';
	}

	/**
	 * Gets the plugin basename as installed.
	 *
	 * @return string
	 */
	public function get_installed_basename() {
		if ( defined( 'JOB_MANAGER_PLUGIN_BASENAME' ) ) {
			return JOB_MANAGER_PLUGIN_BASENAME;
		}

		return false;
	}

	/**
	 * Gets the current plugin version.
	 *
	 * @return string
	 */
	public function get_current_version() {
		if ( ! defined( 'JOB_MANAGER_PLUGIN_BASENAME' ) ) {
			return false;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();

		if ( isset( $all_plugins[ JOB_MANAGER_PLUGIN_BASENAME ] ) ) {
			return $all_plugins[ JOB_MANAGER_PLUGIN_BASENAME ]['Version'];
		}

		return false;
	}

	/**
	 * Gets the message displayed above plugin messages when on beta or RC channel.
	 *
	 * @return string
	 */
	public function get_message_not_stable_notice() {
		return __( '<h1><span>&#9888;</span>This is a pre-release version and should only be used on non-production sites<span>&#9888;</span></h1>', 'wp-job-manager-beta-tester' );
	}

	/**
	 * Gets the changelog displayed on the plugin information.
	 *
	 * @param Plugin_Package $plugin_package Plugin package to get changelog for.
	 * @return string
	 */
	public function get_changelog( $plugin_package ) {
		return sprintf(
			'<p><a target="_blank" href="%s">' . esc_html__( 'Read the changelog and find out more about the release on GitHub.', 'wp-job-manager-beta-tester' ) . '</a></p>',
			$plugin_package->get_changelog_url()
		);
	}

	/**
	 * Get the basic configuration for the plugin.
	 *
	 * @return array
	 */
	protected function get_plugin_base_config() {
		return [
			'name'        => 'WP Job Manager',
			'plugin_name' => 'WP Job Manager',
			'author'      => 'Automattic',
			'homepage'    => 'https://wpjobmanager.com',
			'plugin_file' => $this->get_installed_basename(),
			'slug'        => $this->get_plugin_slug(),
			'sections'    => [
				'description' => esc_html__( 'Manage job listings from the WordPress admin panel, and allow users to post job listings directly to your site.', 'wp-job-manager-beta-tester' ),
			],
		];
	}
}
