<?php
/**
 * File containing the class \WP_Job_Manager_Beta_Tester\Updater\Sources\Github.
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
 * Package containing class to retrieve plugin version packages from GitHub.
 *
 * @class \WP_Job_Manager_Beta_Tester\Updater\Sources\Github
 */
class Github extends Abstract_Source {
	/**
	 * Base prefix for this plugin. Used for caching and determining release with download package.
	 *
	 * @var string
	 */
	private $id_prefix;

	/**
	 * Slug for repo (:org/:repo). For example: `Automattic/sensei`
	 *
	 * @var string
	 */
	private $repo_slug;

	/**
	 * Github constructor.
	 *
	 * @param string $id_prefix Base prefix for this plugin.
	 * @param string $repo_slug   URL to for the GitHub release endpoint.
	 */
	public function __construct( $id_prefix, $repo_slug ) {
		$this->id_prefix = $id_prefix;
		$this->repo_slug = $repo_slug;
	}

	/**
	 * Returns an array of plugin packages.
	 *
	 * @return bool|Plugin_Package[]
	 */
	public function get_versions() {
		$releases_raw = self::api_fetch( $this->id_prefix . '_versions', $this->get_release_download_url() );
		if ( ! $releases_raw ) {
			return false;
		}

		$releases = json_decode( $releases_raw, true );
		if ( ! $releases ) {
			return false;
		}

		$packages = [];
		foreach ( $releases as $release ) {
			if ( ! empty( $release['draft'] ) ) {
				continue;
			}

			$download_package = $this->get_download_package( $release );
			$version          = $this->get_version( $release );

			if ( ! $download_package || ! $version ) {
				continue;
			}

			$package = new Plugin_Package(
				[
					'version'              => $version,
					'is_prerelease'        => $release['prerelease'],
					'release_date'         => $release['published_at'],
					'download_package_url' => $download_package,
					'release_info_url'     => $release['html_url'],
					'changelog_url'        => $this->get_changelog_url( $release['tag_name'] ),
				]
			);

			if ( $package->is_valid() ) {
				$packages[ $version ] = $package;
			}
		}

		return $packages;
	}

	/**
	 * Gets the download package for a release.
	 *
	 * @param array $release Array containing release from GitHub.
	 * @return bool|string
	 */
	private function get_download_package( $release ) {
		if ( empty( $release['assets'] ) ) {
			return false;
		}

		foreach ( $release['assets'] as $asset ) {
			if ( empty( $asset['name'] ) || '.zip' !== substr( $asset['name'], -4 ) ) {
				continue;
			}

			if ( 0 === strpos( $asset['name'], $this->id_prefix ) ) {
				return $asset['browser_download_url'];
			}
		}

		return false;
	}

	/**
	 * Gets the download package for a release.
	 *
	 * @param array $release Array containing release from GitHub.
	 * @return bool|string
	 */
	private function get_version( $release ) {
		if ( empty( $release['tag_name'] ) ) {
			return false;
		}

		return preg_replace( '/^(version|release|v)[\/]?/', '', $release['tag_name'] );
	}

	/**
	 * Gets the URL for the changelog.
	 *
	 * @param string $version_tag Version tag on git.
	 * @return bool|string
	 */
	private function get_changelog_url( $version_tag ) {
		return 'https://raw.githubusercontent.com/' . $this->repo_slug . '/' . $version_tag . '/changelog.txt';
	}

	/**
	 * Gets the URL to fetch the releases.
	 *
	 * @return string
	 */
	private function get_release_download_url() {
		return 'https://api.github.com/repos/' . $this->repo_slug . '/releases';
	}
}
