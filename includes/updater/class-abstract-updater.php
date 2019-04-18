<?php
/**
 * File containing the class \WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater.
 *
 * @package wp-job-manager-beta-tester
 * @since   1.0.0
 */

namespace WP_Job_Manager_Beta_Tester\Updater;

use WP_Job_Manager_Beta_Tester\Updater\Plugin_Package;
use WP_Job_Manager_Beta_Tester\Updater\Sources\Source;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing the shared update logic.
 *
 * @class \WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater
 */
abstract class Abstract_Updater {
	const CHANNEL_BETA   = 'beta';
	const CHANNEL_RC     = 'rc';
	const CHANNEL_STABLE = 'stable';

	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Current channel that we're currently set to follow.
	 *
	 * @var string
	 */
	private $channel;

	/**
	 * Cached return variables.
	 *
	 * @var array
	 */
	private $cache = [];

	/**
	 * Initialize the singleton instance.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->include_dependencies();
	}

	/**
	 * Adds all filters and actions.
	 *
	 * @since 1.0.0
	 *
	 * @param string $channel Current channel (beta, rc, stable).
	 */
	public function init( $channel ) {
		if ( ! in_array( $channel, [ self::CHANNEL_STABLE, self::CHANNEL_BETA, self::CHANNEL_RC ], true ) ) {
			$channel = self::CHANNEL_STABLE;
		}
		$this->channel = $channel;

		// If a recognized copy of the plugin is not installed, we don't want to load our fancy overrides.
		if ( ! $this->get_current_version_package() ) {
			return;
		}

		if ( self::CHANNEL_STABLE !== $this->get_channel() ) {
			// If we aren't on the stable channel, override the update checks.
			add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'api_check' ] );
			add_filter( 'plugins_api', [ $this, 'plugins_api' ], 10, 3 );
			add_filter( 'upgrader_source_selection', [ $this, 'upgrader_source_selection' ], 10, 4 );
		}
	}

	/**
	 * Gets the source object to fetch the version plugin packages.
	 *
	 * @return Source
	 */
	abstract public function get_plugin_package_source();

	/**
	 * Gets the plugin slug.
	 *
	 * @return string
	 */
	abstract public function get_plugin_slug();

	/**
	 * Gets the plugin basename as installed.
	 *
	 * @return string
	 */
	abstract public function get_installed_basename();

	/**
	 * Gets the current plugin version.
	 *
	 * @return string
	 */
	abstract public function get_current_version();

	/**
	 * Get the basic configuration for the plugin.
	 *
	 * @return array
	 */
	abstract protected function get_plugin_base_config();

	/**
	 * Gets the message displayed above plugin messages when on beta or RC channel.
	 *
	 * @return string
	 */
	abstract public function get_message_not_stable_notice();

	/**
	 * Gets the changelog displayed on the plugin information.
	 *
	 * @param Plugin_Package $plugin_package Plugin package to get changelog for.
	 * @return string
	 */
	abstract public function get_changelog( $plugin_package );

	/**
	 * Get all version plugin packages.
	 *
	 * @param callable $filter_callback Callback to filter the versions returned.
	 * @return \WP_Job_Manager_Beta_Tester\Updater\Plugin_Package[]
	 */
	protected function get_versions( $filter_callback = null ) {
		$source_versions = $this->get_plugin_package_source()->get_versions();

		if ( $filter_callback ) {
			$source_versions = array_filter( $source_versions, $filter_callback );
		}

		uasort(
			$source_versions,
			function( $release_a, $release_b ) {
				/**
				 * Release packages to compare.
				 *
				 * @var Plugin_Package $release_a
				 * @var Plugin_Package $release_b
				 */
				return version_compare( $release_a->get_version(), $release_b->get_version(), '<' );
			}
		);

		return $source_versions;
	}

	/**
	 * Get the latest channel release for a particular channel.
	 *
	 * @param string|null $channel Channel to get latest release for. Null for the current channel.
	 * @return bool|mixed|Plugin_Package
	 */
	public function get_latest_channel_release( $channel = null ) {
		if ( null === $channel ) {
			$channel = $this->channel;
		}

		switch ( $channel ) {
			case self::CHANNEL_BETA:
				$releases = $this->get_beta_channel();
				break;
			case self::CHANNEL_RC:
				$releases = $this->get_rc_channel();
				break;
			case self::CHANNEL_STABLE:
				$releases = $this->get_stable_channel();
				break;
			default:
				return false;
		}

		if ( empty( $releases ) ) {
			return false;
		}

		return array_shift( $releases );
	}

	/**
	 * Helper used to cache simple generator results.
	 *
	 * @param string   $id        Generator ID.
	 * @param callable $generator Callable function to generate result.
	 * @return mixed
	 */
	protected function cached_generator_helper( $id, $generator ) {
		if ( isset( $this->cache[ $id ] ) ) {
			return $this->cache[ $id ];
		}

		$this->cache[ $id ] = call_user_func( $generator );

		return $this->cache[ $id ];
	}

	/**
	 * Get version plugin packages for betas, RCs, and stable.
	 *
	 * @return \WP_Job_Manager_Beta_Tester\Updater\Plugin_Package[]
	 */
	public function get_beta_channel() {
		$generator = function() {
			return $this->get_versions();
		};

		return $this->cached_generator_helper( __FUNCTION__, $generator );
	}

	/**
	 * Get version plugin packages for RCs and stable.
	 *
	 * @return \WP_Job_Manager_Beta_Tester\Updater\Plugin_Package[]
	 */
	public function get_rc_channel() {
		$generator = function() {
			return $this->get_versions(
				function( $package ) {
					/**
					 * Package variable.
					 *
					 * @var Plugin_Package $package
					 */
					return $package->is_stable() || $package->is_rc();
				}
			);
		};

		return $this->cached_generator_helper( __FUNCTION__, $generator );
	}

	/**
	 * Get version plugin packages for stable only.
	 *
	 * @return \WP_Job_Manager_Beta_Tester\Updater\Plugin_Package[]
	 */
	public function get_stable_channel() {
		$generator = function() {
			return $this->get_versions(
				function( $package ) {
					/**
					 * Package variable.
					 *
					 * @var Plugin_Package $package
					 */
					return $package->is_stable();
				}
			);
		};

		return $this->cached_generator_helper( __FUNCTION__, $generator );
	}

	/**
	 * Include required files.
	 */
	private function include_dependencies() {
		if ( ! class_exists( '\Plugin_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		if ( ! class_exists( '\Automatic_Upgrader_Skin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';
		}

		include_once __DIR__ . '/class-plugin-package.php';
		include_once __DIR__ . '/class-plugin-upgrader.php';
		include_once __DIR__ . '/sources/interface-source.php';
		include_once __DIR__ . '/sources/class-abstract-source.php';
		include_once __DIR__ . '/sources/class-github.php';
	}

	/**
	 * Hook into the plugin update check and connect to WPorg.
	 *
	 * @since 1.0
	 * @param object $transient The plugin data transient.
	 * @return object $transient Updated plugin data transient.
	 */
	public function api_check( $transient ) {
		$new_version_package = $this->get_latest_channel_release();

		if ( ! $new_version_package ) {
			return $transient;
		}

		// check the version and decide if it's new.
		$update = version_compare( $new_version_package->get_version(), $this->get_current_version(), '>' );

		if ( ! $update ) {
			return $transient;
		}

		$plugin_basename = $this->get_installed_basename();

		// Populate response data.
		if ( ! isset( $transient->response[ $plugin_basename ] ) ) {
			$transient->response[ $plugin_basename ] = (object) $this->get_plugin_base_config();
		}

		$transient->response[ $plugin_basename ]->new_version = $new_version_package->get_version();
		$transient->response[ $plugin_basename ]->zip_url     = $new_version_package->get_download_package_url();
		$transient->response[ $plugin_basename ]->package     = $new_version_package->get_download_package_url();
		unset( $transient->no_update[ $plugin_basename ] );

		return $transient;
	}

	/**
	 * Filters the Plugin Installation API response results.
	 *
	 * @param false|object|array $response The result object or array. Default false.
	 * @param string             $action   The type of information being requested from the Plugin Installation API.
	 * @param object             $args     Plugin API arguments.
	 * @return object|bool
	 */
	public function plugins_api( $response, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $response;
		}

		// Check if this call API is for the right plugin.
		if ( ! isset( $args->slug ) || $args->slug !== $this->get_plugin_slug() ) {
			return $response;
		}

		$new_version_package = $this->get_latest_channel_release();

		if ( ! version_compare( $new_version_package->get_version(), $this->get_current_version(), '>' ) ) {
			return $response;
		}

		$response = (object) $this->get_plugin_base_config();
		$warning  = '';

		if ( ! $new_version_package->is_stable() ) {
			$warning = $this->get_message_not_stable_notice();
		}

		// If we are returning a different version than the stable tag on .org, manipulate the returned data.
		$response->version       = $new_version_package->get_version();
		$response->download_link = $new_version_package->get_download_package_url();

		if ( ! isset( $response->sections ) ) {
			$response->sections = [];
		}
		$response->sections['changelog'] = $this->get_changelog( $new_version_package );

		foreach ( $response->sections as $key => $section ) {
			$response->sections[ $key ] = wp_kses_post( $warning . $section );
		}

		return $response;
	}

	/**
	 * Switch to a specific version of the plugin.
	 *
	 * @param string $new_version New version to switch to.
	 * @return bool
	 *
	 * @throws \Exception When encountering an error.
	 */
	public function switch_version( $new_version ) {
		$versions = $this->get_beta_channel();

		if ( empty( $versions[ $new_version ] ) ) {
			return false;
		}

		$skin     = new \Automatic_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install_plugin_package( $this, $versions[ $new_version ] );

		activate_plugin( $this->get_installed_basename(), '', is_network_admin(), true );

		if ( is_wp_error( $skin->result ) ) {
			throw new \Exception( $skin->result->get_error_message() );
		}

		return $result;
	}

	/**
	 * Rename the downloaded zip to match the currently installed plugin.
	 *
	 * @param string      $source        File source location.
	 * @param string      $remote_source Remote file source location.
	 * @param WP_Upgrader $upgrader      WordPress Upgrader instance.
	 * @param array       $hook_extra    Extra arguments passed to hooked filters.
	 * @return string
	 */
	public function upgrader_source_selection( $source, $remote_source, $upgrader, $hook_extra ) {
		global $wp_filesystem;

		if ( ! isset( $hook_extra['plugin'] ) || $this->get_installed_basename() !== $hook_extra['plugin'] ) {
			return $source;
		}

		$installed_dir = dirname( $this->get_installed_basename() );

		if ( strstr( $source, '/' . $installed_dir ) ) {
			$corrected_source = trailingslashit( dirname( $source ) ) . trailingslashit( $installed_dir );

			if ( $corrected_source === $source ) {
				return $source;
			}

			if ( $wp_filesystem->move( $source, $corrected_source, true ) ) {
				return $corrected_source;
			} else {
				return new \WP_Error( false );
			}
		}

		return $source;
	}

	/**
	 * Gets the plugin package for the current version.
	 *
	 * @return bool|Plugin_Package
	 */
	public function get_current_version_package() {
		$current_version = $this->get_current_version();
		if ( ! $current_version ) {
			return false;
		}

		foreach ( $this->get_beta_channel() as $plugin_package ) {
			if ( $current_version === $plugin_package->get_version() ) {
				return $plugin_package;
			}
		}

		return false;
	}

	/**
	 * Get the current channel.
	 *
	 * @return string
	 */
	public function get_channel() {
		return $this->channel;
	}

	/**
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new static();
		}
		return self::$instance;
	}
}
