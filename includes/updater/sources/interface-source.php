<?php
/**
 * File containing the interface \WP_Job_Manager_Beta_Tester\Updater\Sources\Source.
 *
 * @package wp-job-manager-beta-tester
 * @since   1.0.0
 */

namespace WP_Job_Manager_Beta_Tester\Updater\Sources;

use WP_Job_Manager_Beta_Tester\Updater\Plugin_Package;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface for a version package source.
 *
 * @interface \WP_Job_Manager_Beta_Tester\Updater\Sources\Source
 */
interface Source {
	/**
	 * Returns an array of plugin packages.
	 *
	 * @return bool|Plugin_Package[]
	 */
	public function get_versions();
}
