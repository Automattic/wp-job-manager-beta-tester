<?php
/**
 * File containing the view for the beta testing settings.
 *
 * @package wp-job-manager-beta-tester
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	add_settings_error( 'wp-job-manager-beta-tester-messages', 'wp-job-manager-beta-tester-message', __( 'Settings Saved', 'wp-job-manager-beta-tester' ), 'updated' );
}

$switch_version_result = \WP_Job_Manager_Beta_Tester\Admin::instance()->get_destroy_switch_version_result();
if ( $switch_version_result ) {
	if ( ! empty( $switch_version_result['result'] ) ) {
		echo '<div class="notice notice-success"><p>';
		// translators: placeholder is the version that was just switched to.
		$message = sprintf( __( '<strong>WP Job Manager</strong> was successfully switched to version <strong>%s</strong>.', 'wp-job-manager-beta-tester' ), $switch_version_result['new_version'] );
		echo wp_kses( $message, [ 'strong' => [] ] );
		echo '</p></div>';
	} else {
		echo '<div class="error"><p>';
		// translators: %1$s is the version that was being switched to; %2$s is the error message that was passed back.
		$message = sprintf( __( 'An error occurred while switching <strong>WP Job Manager</strong> to version <strong>%1$s</strong>: %2$s', 'wp-job-manager-beta-tester' ), $switch_version_result['new_version'], $switch_version_result['error_message'] );
		echo wp_kses( $message, [ 'strong' => [] ] );
		echo '</p></div>';
	}
}

// show error/update messages.
settings_errors( 'wp-job-manager-beta-tester-messages' );

$current_version = \WP_Job_Manager_Beta_Tester\Updater::instance()->get_current_version_package();
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div class="notice notice-info">
		<p>
			<?php
			echo wp_kses(
				// translators: placeholder is version of WP Job Manager currently installed.
				sprintf( __( 'You currently have <strong>WP Job Manager %s</strong> installed.', 'wp-job-manager-beta-tester' ), $current_version->get_version() ),
				[ 'strong' => [] ]
			)
			?>
			<?php esc_html_e( 'Please report any issues you encounter in pre-release versions of WP Job Manager to the GitHub repository.', 'wp-job-manager-beta-tester' ); ?>
		</p>
		<p>
			<?php
			if ( $current_version->get_release_info_url() ) {
				printf(
					'<a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a> ',
					esc_url( $current_version->get_release_info_url() ),
					esc_html__( 'Release Information', 'wp-job-manager-beta-tester' ),
					/* translators: accessibility text */
					esc_html__( '(opens in a new tab)', 'wp-job-manager-beta-tester' )
				);
			}
			if ( $current_version->get_changelog_url() ) {
				printf(
					'<a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a> ',
					esc_url( $current_version->get_changelog_url() ),
					esc_html__( 'View Changelog', 'wp-job-manager-beta-tester' ),
					/* translators: accessibility text */
					esc_html__( '(opens in a new tab)', 'wp-job-manager-beta-tester' )
				);
			}
			printf(
				'<a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a> ',
				esc_url( \WP_Job_Manager_Beta_Tester\WP_Job_Manager_Beta_Tester::WP_JOB_MANAGER_BETA_TESTER_REPORT_ISSUE_URL ),
				esc_html__( 'Report Issue', 'wp-job-manager-beta-tester' ),
				/* translators: accessibility text */
				esc_html__( '(opens in a new tab)', 'wp-job-manager-beta-tester' )
			);
			?>

		</p>
	</div>

	<div class="postbox">
		<div class="inside" style="margin-bottom:0;">
			<form action="options.php" method="post">
				<?php

				settings_fields( 'wp-job-manager-beta-tester' );
				do_settings_sections( 'wp-job-manager-beta-tester' );
				submit_button();

				?>
			</form>
		</div>
	</div>

	<div class="postbox">
		<div class="inside" style="margin-bottom:0;">
			<h2><?php esc_html_e( 'Switch WP Job Manager Version', 'wp-job-manager-beta-tester' ); ?></h2>
			<?php
			$confirm_message = esc_html__( 'Are you sure you want to switch your WP Job Manager version? We do not recommend doing this on production sites. Back up first.', 'wp-job-manager-beta-tester' );
			?>
			<form action="plugins.php?page=wp-job-manager-beta-tester" method="post" onsubmit="return confirm( '<?php echo esc_attr( $confirm_message ); ?>' );">
				<p><?php esc_html_e( 'Use this to manually switch to a particular version of WP Job Manager.', 'wp-job-manager-beta-tester' ); ?></p>
				<label>
					<select id="wp-job-manager-beta-tester-version-select" name="wp_job_manager_beta_version_select">
						<?php
						foreach ( \WP_Job_Manager_Beta_Tester\Updater::instance()->get_beta_channel() as $package ) {
							echo sprintf( '<option name="%1$s">%1$s</option>', esc_html( $package->get_version() ) );
						}
						?>
					</select>
				</label>
				<br>
				<?php
				wp_nonce_field( 'switch-wp-job-manager-version' );
				submit_button( esc_html__( 'Switch Version', 'wp-job-manager-beta-tester' ) );
				?>
			</form>
		</div>
	</div>
</div>
