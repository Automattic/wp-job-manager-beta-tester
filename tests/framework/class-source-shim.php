<?php
/**
 * File containing the class \WP_Job_Manager_Beta_Tester\Updater\Tests\Framework.
 *
 * @package wp-job-manager-beta-tester
 * @since   1.0.0
 */

namespace WP_Job_Manager_Beta_Tester\Tests\Framework;

use WP_Job_Manager_Beta_Tester\Updater\Plugin_Package;
use WP_Job_Manager_Beta_Tester\Updater\Sources\Abstract_Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Package containing class to retrieve plugin version packages from GitHub.
 *
 * @class \WP_Job_Manager_Beta_Tester\Updater\Tests\Framework
 */
class Source_Shim extends Abstract_Source {
	/**
	 * Contains releases to return.
	 * @var array
	 */
	private $releases = [];

	/**
	 * Source_Shim constructor.
	 *
	 * @param $releases
	 */
	public function __construct( $releases ) {
		$this->releases = $releases;
	}

	/**
	 * Returns an array of plugin packages.
	 *
	 * @return bool|Plugin_Package[]
	 */
	public function get_versions() {
		$packages = [];
		foreach ( $this->releases as $version => $release ) {
			$release['version'] = $version;
			$package            = new Plugin_Package( $release );

			if ( $package->is_valid() ) {
				$packages[ $version ] = $package;
			}
		}

		return $packages;
	}

}
