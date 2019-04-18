<?php
/**
 * File containing mocks used throughout all tests.
 *
 * @package wp-job-manager-beta-tester/Tests
 * @since   1.0.0
 */

class Plugin_Upgrader {}

class Automatic_Upgrader_Skin {}

WP_Mock::userFunction( 'plugin_basename' )->andReturn( 'test-plugin-beta/test-plugin-beta.php' );

WP_Mock::userFunction( 'plugins_url' )->andReturn( 'http://example.com/wp-content/plugins' );

WP_Mock::passthruFunction( 'untrailingslashit' );

if ( ! function_exists( 'wp_parse_args' ) ) {
	function wp_parse_args( $arr1, $arr2 ) {
		if ( ! is_array( $arr1 ) || ! is_array( $arr2 ) ) {
			throw new \Exception( 'Mocked `wp_parse_args` can only handle arrays' );
		}
		return array_merge( $arr2, $arr1 );
	}
}
