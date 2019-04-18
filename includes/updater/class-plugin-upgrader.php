<?php
/**
 * File containing the class \WP_Job_Manager_Beta_Tester\Updater_Base.
 *
 * @package wp-job-manager-beta-tester
 * @since   1.0.0
 */

namespace WP_Job_Manager_Beta_Tester\Updater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WC_Beta_Tester_Plugin_Upgrader
 */
class Plugin_Upgrader extends \Plugin_Upgrader {

	/**
	 * Install a plugin package.
	 *
	 * @param Abstract_Updater $updater        Updater object.
	 * @param Plugin_Package   $plugin_package Plugin package to install.
	 * @return array|bool|\WP_Error
	 */
	public function install_plugin_package( Abstract_Updater $updater, Plugin_Package $plugin_package ) {
		$this->init();
		$this->upgrade_strings();

		add_filter( 'upgrader_pre_install', [ $this, 'deactivate_plugin_before_upgrade' ], 10, 2 );
		add_filter( 'upgrader_clear_destination', [ $this, 'delete_old_plugin' ], 10, 4 );

		$this->run(
			[
				'package'           => $plugin_package->get_download_package_url(),
				'destination'       => WP_PLUGIN_DIR,
				'clear_destination' => true,
				'clear_working'     => true,
				'hook_extra'        => [
					'plugin' => $updater->get_installed_basename(),
					'type'   => 'plugin',
					'action' => 'update',
				],
			]
		);

		// Cleanup our hooks, in case something else does a upgrade on this connection.
		remove_filter( 'upgrader_pre_install', [ $this, 'deactivate_plugin_before_upgrade' ] );
		remove_filter( 'upgrader_clear_destination', [ $this, 'delete_old_plugin' ] );

		if ( ! $this->result || is_wp_error( $this->result ) ) {
			return $this->result;
		}

		wp_clean_plugins_cache( true );

		return true;
	}

}
