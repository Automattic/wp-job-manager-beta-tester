<?php
/**
 * File containing the class \WP_Job_Manager_Beta_Tester\Tests\Framework\Updater_Shim.
 *
 * @package wp-job-manager-beta-tester/Tests
 * @since   1.0.0
 */

namespace WP_Job_Manager_Beta_Tester\Tests\Framework;

use WP_Job_Manager_Beta_Tester\Updater\Plugin_Package;
use WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater;
use WP_Job_Manager_Beta_Tester\Updater\Sources\Source;

/**
 * Class containing implemented version of Abstract_Updater.
 *
 * @class \WP_Job_Manager_Beta_Tester\Tests\Framework\Updater_Shim
 */
class Updater_Shim extends Abstract_Updater {
	/**
	 * Set the source object.
	 *
	 * @param Source $source Source file to use
	 */
	public function __construct( $source ) {
		$this->source = $source;
	}

	/**
	 * Get all version plugin packages.
	 *
	 * @param callable $filter_callback Callback to filter the versions returned.
	 * @return \WP_Job_Manager_Beta_Tester\Updater\Plugin_Package[]
	 */
	public function get_versions( $filter_callback = null ) {
		return parent::get_versions( $filter_callback );
	}

	/**
	 * Gets the source object to fetch the version plugin packages.
	 *
	 * @return Source
	 */
	public function get_plugin_package_source() {
		return $this->source;
	}

	/**
	 * Gets the plugin slug.
	 *
	 * @return string
	 */
	public function get_plugin_slug() {
		return 'test-plugin';
	}

	/**
	 * Gets the plugin basename as installed.
	 *
	 * @return string
	 */
	public function get_installed_basename() {
		return 'test-plugin/test-plugin.php';
	}

	/**
	 * Gets the current plugin version.
	 *
	 * @return string
	 */
	public function get_current_version() {
		return '1.1.0';
	}

	/**
	 * Gets the message displayed above plugin messages when on beta or RC channel.
	 *
	 * @return string
	 */
	public function get_message_not_stable_notice() {
		return '%not-stable-notice%';
	}

	/**
	 * Gets the changelog displayed on the plugin information.
	 *
	 * @param Plugin_Package $plugin_package Plugin package to get changelog for.
	 * @return string
	 */
	public function get_changelog( $plugin_package ) {
		return '%changelog-notice%';
	}

	/**
	 * Get the basic configuration for the plugin.
	 *
	 * @return array
	 */
	protected function get_plugin_base_config() {
		return [
			'name'        => 'Test Plugin',
			'plugin_name' => 'Test Plugin',
			'author'      => 'Test Org',
			'homepage'    => 'https://example.com',
			'plugin_file' => $this->get_installed_basename(),
			'slug'        => $this->get_plugin_slug(),
			'sections'    => [
				'description' => '%description-message%',
			],
		];
	}
}
