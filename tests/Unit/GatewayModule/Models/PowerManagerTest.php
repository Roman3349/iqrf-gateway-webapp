<?php
/**
 * TEST: App\GatewayModule\Models\PowerManager
 * @covers App\GatewayModule\Models\PowerManager
 * @phpVersion >= 7.2
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Models\PowerManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for tool for powering off and rebooting IQRF Gateway
 */
final class PowerManagerTest extends CommandTestCase {

	/**
	 * @var PowerManager Tool for powering off and rebooting IQRF Gateway
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new PowerManager($this->commandManager);
	}

	/**
	 * Tests the function to power off IQRF Gateway
	 */
	public function testPowerOff(): void {
		$this->receiveCommand('shutdown -P `date --date "now + 60 seconds" "+%H:%M"`', true);
		Assert::noError(function (): void {
			$this->manager->powerOff();
		});
	}

	/**
	 * Tests the function to reboot IQRF Gateway
	 */
	public function testReboot(): void {
		$this->receiveCommand('shutdown -r `date --date "now + 60 seconds" "+%H:%M"`', true);
		Assert::noError(function (): void {
			$this->manager->reboot();
		});
	}

}

$test = new PowerManagerTest();
$test->run();
