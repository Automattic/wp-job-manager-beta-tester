<?php
/**
 * File containing the class \WP_Job_Manager_Beta_Tester\Admin.
 *
 * @package wp-job-manager-beta-tester
 * @since   1.0.0
 */

namespace WP_Job_Manager_Beta_Tester;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing settings for the plugin.
 *
 * @class \WP_Job_Manager_Beta_Tester\Admin
 */
final class Admin {
	const TRANSIENT_SWITCH_VERSION_RESULT = 'wp-job-manager-switch-version-result';

	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Initialize the singleton instance.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
	}

	/**
	 * Adds all filters and actions.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$current_version = \WP_Job_Manager_Beta_Tester\Updater::instance()->get_current_version_package();

		if ( false === $current_version ) {
			add_action(
				'plugin_row_meta',
				function( $plugin_meta, $plugin_file ) {
					if ( WP_JOB_MANAGER_BETA_TESTER_PLUGIN_BASENAME !== $plugin_file ) {
						return $plugin_meta;
					}

					$message       = '<span style="color: red; font-weight: bold;">';
					$message      .= esc_html__( 'Requires WP Job Manager to be installed and activated.', 'wp-job-manager-beta-tester' );
					$message      .= '</span>';
					$plugin_meta[] = $message;

					return $plugin_meta;
				},
				10,
				2
			);

			return;
		}

		add_action( 'admin_init', [ $this, 'init_settings' ] );
		add_action( 'admin_init', [ $this, 'handle_version_switch' ] );
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'plugin_action_links_' . WP_JOB_MANAGER_BETA_TESTER_PLUGIN_BASENAME, [ $this, 'add_settings_link' ], 10, 2 );
		add_action( 'network_admin_plugin_action_links_' . WP_JOB_MANAGER_BETA_TESTER_PLUGIN_BASENAME, [ $this, 'add_settings_link' ], 10, 2 );
	}

	/**
	 * Get plugin settings.
	 *
	 * @return object
	 */
	public static function get_settings() {
		$settings              = (object) wp_parse_args(
			get_option( 'wp_job_manager_beta_options', [] ),
			[
				'channel'     => 'beta',
				'auto_update' => false,
			]
		);
		$settings->auto_update = (bool) $settings->auto_update;

		return $settings;
	}

	/**
	 * Handle the version switching.
	 */
	public function handle_version_switch() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}
		if ( empty( $_POST['wp_job_manager_beta_version_select'] ) || empty( $_POST['_wpnonce'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Don't touch the nonce.
		if ( ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'switch-wp-job-manager-version' ) ) {
			wp_die( esc_html__( 'Action failed. Please go back and retry.', 'wp-job-manager-beta-tester' ) );
			return;
		}

		$new_version = isset( $_POST['wp_job_manager_beta_version_select'] ) ? sanitize_text_field( wp_unslash( $_POST['wp_job_manager_beta_version_select'] ) ) : false;

		if ( empty( $new_version ) ) {
			return;
		}

		$result        = false;
		$error_message = false;
		try {
			$result = Updater::instance()->switch_version( $new_version );
		} catch ( \Exception $e ) {
			$error_message = $e->getMessage();
		}

		if ( empty( $error_message ) && false === $result ) {
			// translators: Placeholder is the version the user is trying to switch to.
			$result = sprintf( esc_html__( 'An error occurred while attempting to switch the version to %s', 'wp-job-manager-beta-tester' ), $new_version );
		}

		set_site_transient(
			self::TRANSIENT_SWITCH_VERSION_RESULT,
			wp_json_encode(
				[
					'result'        => $result,
					'error_message' => $error_message,
					'new_version'   => $new_version,
				]
			),
			60 * 60
		);

		wp_safe_redirect( admin_url( 'plugins.php?page=wp-job-manager-beta-tester' ) );
		exit;
	}

	/**
	 * Get and destroy any pending switch version results.
	 *
	 * @return array|bool
	 */
	public function get_destroy_switch_version_result() {
		$result = get_site_transient( self::TRANSIENT_SWITCH_VERSION_RESULT );

		if ( ! $result ) {
			return false;
		}

		delete_site_transient( self::TRANSIENT_SWITCH_VERSION_RESULT );

		return json_decode( $result, true );
	}

	/**
	 * Initialise settings
	 */
	public function init_settings() {
		register_setting( 'wp-job-manager-beta-tester', 'wp_job_manager_beta_options' );

		add_settings_section(
			'wp-job-manager-beta-tester-update',
			__( 'Settings', 'wp-job-manager-beta-tester' ),
			[ $this, 'update_section_html' ],
			'wp-job-manager-beta-tester'
		);

		add_settings_field(
			'wp-job-manager-beta-tester-channel',
			__( 'Release Channel', 'wp-job-manager-beta-tester' ),
			[ $this, 'version_select_html' ],
			'wp-job-manager-beta-tester',
			'wp-job-manager-beta-tester-update',
			[
				'label_for' => 'channel',
			]
		);
	}

	/**
	 * Update section HTML output.
	 *
	 * @param array $args Arguments.
	 */
	public function update_section_html( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'The following settings allow you to choose which WP Job Manager updates to receive on this site, including beta and RC versions not quite ready for production deployment.', 'wp-job-manager-beta-tester' ); ?></p>
		<?php
	}

	/**
	 * Version select markup output.
	 *
	 * @param array $args Arguments.
	 */
	public function version_select_html( $args ) {
		$settings = self::get_settings();
		$channels = [
			Updater::CHANNEL_BETA   => [
				'name'        => __( 'Beta Releases', 'wp-job-manager-beta-tester' ),
				'description' => __( 'Beta releases contain experimental functionality for testing purposes only. This channel will also include RC and stable releases if more current.', 'wp-job-manager-beta-tester' ),
				'latest'      => Updater::instance()->get_latest_channel_release( Updater::CHANNEL_BETA ),
			],
			Updater::CHANNEL_RC     => [
				'name'        => __( 'Release Candidates', 'wp-job-manager-beta-tester' ),
				'description' => __( 'Release candidates are released to ensure any critical problems have not gone undetected. This channel will also include stable releases if more current.', 'wp-job-manager-beta-tester' ),
				'latest'      => Updater::instance()->get_latest_channel_release( Updater::CHANNEL_RC ),
			],
			Updater::CHANNEL_STABLE => [
				'name'        => __( 'Stable Releases', 'wp-job-manager-beta-tester' ),
				'description' => __( 'This is the default behavior in WordPress.', 'wp-job-manager-beta-tester' ),
				'latest'      => Updater::instance()->get_latest_channel_release( Updater::CHANNEL_STABLE ),
			],
		];
		echo '<fieldset><legend class="screen-reader-text"><span>' . esc_html__( 'Update Channel', 'wp-job-manager-beta-tester' ) . '</span></legend>';
		foreach ( $channels as $channel_id => $channel ) {
			?>
			<label>
				<input type="radio" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="wp_job_manager_beta_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr( $channel_id ); ?>" <?php checked( $settings->{ $args['label_for'] }, $channel_id ); ?> />
				<?php
				$update_time = ( $channel['latest'] && $channel['latest']->get_release_date() )
					// translators: %s placeholder is relative time since last update.
					? sprintf( __( 'Last updated %s ago', 'wp-job-manager-beta-tester' ), human_time_diff( strtotime( $channel['latest']->get_release_date() ) ) )
					: false;
				?>
				<?php echo esc_html( $channel['name'] ); ?>
				<?php
				if ( $update_time ) {
					echo '<small>(' . esc_html( $update_time ) . ')</small>';
				}
				?>
				<p class="description">
					<?php echo esc_html( $channel['description'] ); ?>
				</p>
			</label>
			<br>
			<?php
		}
		echo '</fieldset>';
	}

	/**
	 * Adds the admin menu item under Plugins.
	 */
	public function add_admin_menu() {
		add_plugins_page( esc_html__( 'WP Job Manager Beta Tester', 'wp-job-manager-beta-tester' ), esc_html__( 'WP Job Manager Beta Tester', 'wp-job-manager-beta-tester' ), 'install_plugins', 'wp-job-manager-beta-tester', [ $this, 'output_settings_page' ] );
	}

	/**
	 * Add link to settings page for this plugin.
	 *
	 * @param array  $actions     Actions to show in plugin list.
	 * @param string $plugin_file Plugin file currently being listed.
	 *
	 * @return mixed
	 */
	public function add_settings_link( $actions, $plugin_file ) {
		if ( WP_JOB_MANAGER_BETA_TESTER_PLUGIN_BASENAME !== $plugin_file ) {
			return $actions;
		}

		$new_actions             = [];
		$new_actions['settings'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'plugins.php?page=wp-job-manager-beta-tester' ) ),
			esc_html__( 'Settings', 'wp-job-manager-beta-tester' )
		);

		return array_merge( $new_actions, $actions );
	}

	/**
	 * Output the settings page content.
	 */
	public function output_settings_page() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		include __DIR__ . '/views/html-beta-settings.php';
	}

	/**
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

}
