<?php
/**
 * File containing the class \WP_Job_Manager_Beta_Tester\Updater\Sources\Abstract_Source.
 *
 * @package wp-job-manager-beta-tester
 * @since   1.0.0
 */

namespace WP_Job_Manager_Beta_Tester\Updater\Sources;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Package containing base class to retrieve plugin version packages.
 *
 * @class \WP_Job_Manager_Beta_Tester\Updater\Sources\Abstract_Source
 */
abstract class Abstract_Source implements Source {
	/**
	 * Fetch API response if not cached.
	 *
	 * @param string $id           Unique ID for this request (used to cache request).
	 * @param string $url          URL to fetch.
	 * @param array  $args         Arguments to pass on to `wp_safe_remote_get()`.
	 * @param bool   $ignore_cache Ignore the cached value.
	 *
	 * @return bool|string
	 */
	protected static function api_fetch( $id, $url, $args = [], $ignore_cache = false ) {
		$cache_key = 'cached_' . $id;
		$data      = get_site_transient( $cache_key );
		if ( ! $ignore_cache && $data ) {
			return $data;
		}

		$response = wp_safe_remote_get( $url, $args );

		if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
			return false;
		}

		$data = $response['body'];
		set_site_transient( $cache_key, $data, HOUR_IN_SECONDS * 3 );

		return $data;
	}
}
