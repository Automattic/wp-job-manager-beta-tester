<?php
/**
 * File containing the class \WP_Job_Manager_Beta_Tester\Tests\Unit_Tests\Abstract_Updater.
 *
 * @package wp-job-manager-beta-tester/Tests
 * @since   1.0.0
 */

namespace WP_Job_Manager_Beta_Tester\Tests\Unit_Tests;

use WP_Job_Manager_Beta_Tester\Tests\Framework\Base_Test;
use WP_Job_Manager_Beta_Tester\Tests\Framework\Updater_Shim;
use WP_Job_Manager_Beta_Tester\Tests\Framework\Source_Shim;
use WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater;
use WP_Job_Manager_Beta_Tester\Updater\Plugin_Package;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing base test.
 *
 * @class \WP_Job_Manager_Beta_Tester\Tests\Unit_Tests\Abstract_Updater
 */
class Test_Abstract_Updater extends Base_Test {
	/**
	 * @covers \WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater::get_versions
	 */
	public function testGetVersionsSimple() {
		$updater = $this->getSimpleUpdater();

		$result = $updater->get_versions( null, true );

		$versions = [ '1.1.1-beta.1', '1.1.0-rc.2', '1.1.0-rc.1', '1.1.0-beta.2', '1.1.0-beta.1', '1.0.1', '1.0.1-beta.1', '1.0.0' ];
		$this->assertEquals( $versions, array_keys( $result ) );
	}

	/**
	 * @covers \WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater::get_latest_channel_release
	 * @covers \WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater::get_versions
	 * @covers \WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater::get_beta_channel
	 */
	public function testGetLatestChannelReleaseBeta() {
		$updater = $this->getSimpleUpdater();

		$latest = $updater->get_latest_channel_release( Abstract_Updater::CHANNEL_BETA );

		$this->assertTrue( $latest instanceof Plugin_Package );
		$this->assertEquals( '1.1.1-beta.1', $latest->get_version() );
	}

	/**
	 * @covers \WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater::get_latest_channel_release
	 * @covers \WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater::get_versions
	 * @covers \WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater::get_rc_channel
	 */
	public function testGetLatestChannelReleaseRC() {
		$updater = $this->getSimpleUpdater();

		$latest = $updater->get_latest_channel_release( Abstract_Updater::CHANNEL_RC );

		$this->assertTrue( $latest instanceof Plugin_Package );
		$this->assertEquals( '1.1.0-rc.2', $latest->get_version() );
	}

	/**
	 * @covers \WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater::get_latest_channel_release
	 * @covers \WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater::get_versions
	 * @covers \WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater::get_stable_channel
	 */
	public function testGetLatestChannelReleaseStable() {
		$updater = $this->getSimpleUpdater();

		$latest = $updater->get_latest_channel_release( Abstract_Updater::CHANNEL_STABLE );

		$this->assertTrue( $latest instanceof Plugin_Package );
		$this->assertEquals( '1.0.1', $latest->get_version() );
	}

	/**
	 * @covers \WP_Job_Manager_Beta_Tester\Updater\Abstract_Updater::get_latest_channel_release
	 */
	public function testGetLatestChannelReleaseBad() {
		$updater = $this->getSimpleUpdater();

		$latest = $updater->get_latest_channel_release( 'bad' );

		$this->assertFalse( $latest );
	}

	/**
	 * @return Updater_Shim
	 */
	protected function getSimpleUpdater() {
		return new Updater_Shim( $this->getSimpleSourceShim() );
	}

	/**
	 * @return Source_Shim
	 */
	protected function getSimpleSourceShim() {
		$releases = [
			'1.0.0'        => [
				'is_prerelease'        => false,
				'release_date'         => '2019-01-01',
				'download_package_url' => 'http://example.com/releases/1.0.0/test-plugin.zip',
				'release_info_url'     => 'http://example.com/releases/1.0.0',
				'changelog_url'        => 'http://example.com/releases/1.0.0/changelog.txt',
			],
			'1.0.1'        => [
				'is_prerelease'        => false,
				'release_date'         => '2019-01-01',
				'download_package_url' => 'http://example.com/releases/1.0.1/test-plugin.zip',
				'release_info_url'     => 'http://example.com/releases/1.0.1',
				'changelog_url'        => 'http://example.com/releases/1.0.1/changelog.txt',
			],
			'1.0.1-beta.1' => [
				'is_prerelease'        => false,
				'release_date'         => '2019-01-01',
				'download_package_url' => 'http://example.com/releases/1.0.1/test-plugin.zip',
				'release_info_url'     => 'http://example.com/releases/1.0.1',
				'changelog_url'        => 'http://example.com/releases/1.0.1/changelog.txt',
			],
			'1.1.0-beta.1' => [
				'is_prerelease'        => true,
				'release_date'         => '2019-01-02',
				'download_package_url' => 'http://example.com/releases/1.1.0-beta.1/test-plugin.zip',
				'release_info_url'     => 'http://example.com/releases/1.1.0-beta.1',
				'changelog_url'        => 'http://example.com/releases/1.1.0-beta.1/changelog.txt',
			],
			'1.1.0-beta.2' => [
				'is_prerelease'        => true,
				'release_date'         => '2019-01-03',
				'download_package_url' => 'http://example.com/releases/1.1.0-beta.2/test-plugin.zip',
				'release_info_url'     => 'http://example.com/releases/1.1.0-beta.2',
				'changelog_url'        => 'http://example.com/releases/1.1.0-beta.2/changelog.txt',
			],
			'1.1.0-rc.1'   => [
				'is_prerelease'        => true,
				'release_date'         => '2019-01-03',
				'download_package_url' => 'http://example.com/releases/1.1.0-rc.1/test-plugin.zip',
				'release_info_url'     => 'http://example.com/releases/1.1.0-rc.1',
				'changelog_url'        => 'http://example.com/releases/1.1.0-rc.1/changelog.txt',
			],
			'1.1.0-rc.2'   => [
				'is_prerelease'        => true,
				'release_date'         => '2019-01-04',
				'download_package_url' => 'http://example.com/releases/1.1.0-rc.2/test-plugin.zip',
				'release_info_url'     => 'http://example.com/releases/1.1.0-rc.2',
				'changelog_url'        => 'http://example.com/releases/1.1.0-rc.2/changelog.txt',
			],
			'1.1.1-beta.1' => [
				'is_prerelease'        => true,
				'release_date'         => '2019-01-07',
				'download_package_url' => 'http://example.com/releases/1.1.1-beta.1/test-plugin.zip',
				'release_info_url'     => 'http://example.com/releases/1.1.1-beta.1',
				'changelog_url'        => 'http://example.com/releases/1.1.1-beta.1/changelog.txt',
			],
		];

		uksort(
			$releases,
			function ( $a, $b ) {
				return mt_rand( -10, 10 );
			}
		);

		return new Source_Shim( $releases );
	}
}
