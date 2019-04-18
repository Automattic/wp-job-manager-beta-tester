<?php
/**
 * File containing the class \WP_Job_Manager_Beta_Tester\Tests\Framework\Base_Test.
 *
 * @package wp-job-manager-beta-tester/Tests
 * @since   1.0.0
 */

namespace WP_Job_Manager_Beta_Tester\Tests\Framework;

use WP_Job_Manager_Beta_Tester\Updater;

/**
 * Class containing base test.
 *
 * @class \WP_Job_Manager_Beta_Tester\Tests\Framework\Base_Test
 */
class Base_Test extends \WP_Mock\Tools\TestCase {
	public function setUp() : void {
		\WP_Mock::setUp();
	}

	public function tearDown() : void {
		\WP_Mock::tearDown();
	}

}
