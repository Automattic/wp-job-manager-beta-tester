<?php
/**
 * File containing the class \WP_Job_Manager_Beta_Tester\Updater\Plugin_Package.
 *
 * @package wp-job-manager-beta-tester
 * @since   1.0.0
 */

namespace WP_Job_Manager_Beta_Tester\Updater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Package
 *
 * @class \WP_Job_Manager_Beta_Tester\Updater\Plugin_Package
 */
class Plugin_Package {
	/**
	 * Version number for the release.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * URL of the release zip file.
	 *
	 * @var string
	 */
	private $download_package_url;

	/**
	 * URL of the release info page.
	 *
	 * @var string
	 */
	private $release_info_url;

	/**
	 * URL of the changelog page.
	 *
	 * @var string
	 */
	private $changelog_url;

	/**
	 * Date this version was released.
	 *
	 * @var string
	 */
	private $release_date;

	/**
	 * Force prerelease status.
	 *
	 * @var bool
	 */
	private $is_prerelease;

	/**
	 * Plugin_Package constructor.
	 *
	 * @param array $args Arguments to build package with.
	 */
	public function __construct( $args ) {
		$args = wp_parse_args(
			$args,
			[
				'version'              => false,
				'is_prerelease'        => null,
				'download_package_url' => false,
				'release_info_url'     => false,
				'changelog_url'        => false,
				'release_date'         => false,
			]
		);

		$this->version              = $args['version'];
		$this->is_prerelease        = $args['is_prerelease'];
		$this->download_package_url = $args['download_package_url'];
		$this->release_info_url     = $args['release_info_url'];
		$this->changelog_url        = $args['changelog_url'];
		$this->release_date         = $args['release_date'];
	}

	/**
	 * Get the version for the package.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Check if package is a stable release.
	 *
	 * @return bool
	 */
	public function is_stable() {
		if ( true === $this->is_prerelease ) {
			return false;
		}

		return ! $this->is_beta() && ! $this->is_rc();
	}

	/**
	 * Check if package is a beta release.
	 *
	 * @return bool
	 */
	public function is_beta() {
		return 1 === preg_match( '/[\.\-]beta[\.\-]?[0-9]*$/i', $this->get_version() );
	}

	/**
	 * Check if package is a release candidate.
	 *
	 * @return bool
	 */
	public function is_rc() {
		return 1 === preg_match( '/[\.\-]rc[\.\-]?[0-9]*$/i', $this->get_version() );
	}

	/**
	 * Get the download package URL.
	 *
	 * @return string
	 */
	public function get_download_package_url() {
		return $this->download_package_url;
	}

	/**
	 * Get the release info URL.
	 *
	 * @return string
	 */
	public function get_release_info_url() {
		return $this->release_info_url;
	}

	/**
	 * Get the changelog URL.
	 *
	 * @return string
	 */
	public function get_changelog_url() {
		return $this->changelog_url;
	}

	/**
	 * Get the release date.
	 *
	 * @return int
	 */
	public function get_release_date() {
		return $this->release_date;
	}

	/**
	 * Check if plugin package is complete enough.
	 *
	 * @return bool
	 */
	public function is_valid() {
		if ( false === $this->get_download_package_url() ) {
			return false;
		}
		if ( false === $this->get_version() ) {
			return false;
		}
		return true;
	}
}
