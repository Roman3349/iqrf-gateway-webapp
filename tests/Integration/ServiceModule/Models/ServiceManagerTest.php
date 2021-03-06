<?php

/**
 * TEST: App\ServiceModule\Models\ServiceManager
 * @covers App\ServiceModule\Models\ServiceManager
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\ServiceModule\Models;

use App\ServiceModule\Exceptions\UnsupportedInitSystemException;
use App\ServiceModule\Models\ServiceManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for service manager
 */
final class ServiceManagerTest extends CommandTestCase {

	/**
	 * @var ServiceManager Service manager for systemD init daemon
	 */
	private $managerSystemD;

	/**
	 * @var ServiceManager Service manager for unknown init daemon
	 */
	private $managerUnknown;

	/**
	 * Name of service
	 */
	private const SERVICE_NAME = 'iqrf-gateway-daemon';

	/**
	 * Tests the function to disable the service via systemD
	 */
	public function testDisableSystemD(): void {
		$commands = [
			'systemctl disable ' . self::SERVICE_NAME . '.service',
			'systemctl stop ' . self::SERVICE_NAME . '.service',
		];
		foreach ($commands as $command) {
			$this->receiveCommand($command, true);
		}
		Assert::noError(function (): void {
			$this->managerSystemD->disable();
		});
	}

	/**
	 * Tests the function to disable the service via unknown init daemon
	 */
	public function testDisableUnknown(): void {
		Assert::exception(function (): void {
			$this->managerUnknown->disable();
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to enable the service via systemD
	 */
	public function testEnableSystemD(): void {
		$commands = [
			'systemctl enable ' . self::SERVICE_NAME . '.service',
			'systemctl start ' . self::SERVICE_NAME . '.service',
		];
		foreach ($commands as $command) {
			$this->receiveCommand($command, true);
		}
		Assert::noError(function (): void {
			$this->managerSystemD->enable();
		});
	}

	/**
	 * Tests the function to enable the service via unknown init daemon
	 */
	public function testEnableUnknown(): void {
		Assert::exception(function (): void {
			$this->managerUnknown->enable();
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to check if the service is active via systemD
	 */
	public function testIsActiveSystemD(): void {
		$command = 'systemctl is-active ' . self::SERVICE_NAME . '.service';
		$this->receiveCommand($command, true, 'active');
		Assert::true($this->managerSystemD->isActive());
	}

	/**
	 * Tests the function to check if the service is active via unknown init daemon
	 */
	public function testIsActiveUnknown(): void {
		Assert::exception(function (): void {
			$this->managerUnknown->isActive();
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to check if the service is enabled via systemD
	 */
	public function testIsEnabledSystemD(): void {
		$command = 'systemctl is-enabled ' . self::SERVICE_NAME . '.service';
		$this->receiveCommand($command, true, 'enabled');
		Assert::true($this->managerSystemD->isEnabled());
	}

	/**
	 * Tests the function to check if the service is enabled via unknown init daemon
	 */
	public function testIsEnabledUnknown(): void {
		Assert::exception(function (): void {
			$this->managerUnknown->isEnabled();
		}, UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to start the service via systemD
	 */
	public function testStartSystemD(): void {
		$command = 'systemctl start ' . self::SERVICE_NAME . '.service';
		$this->receiveCommand($command, true);
		Assert::noError(function (): void {
			$this->managerSystemD->start();
		});
	}

	/**
	 * Tests the function to start the service via unknown init daemon
	 */
	public function testStartUnknown(): void {
		Assert::exception([$this->managerUnknown, 'start'], UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to stop the service via systemD
	 */
	public function testStopSystemD(): void {
		$command = 'systemctl stop ' . self::SERVICE_NAME . '.service';
		$this->receiveCommand($command, true);
		Assert::noError([$this->managerSystemD, 'stop']);
	}

	/**
	 * Tests the function to stop the service via unknown init daemon
	 */
	public function testStopUnknown(): void {
		Assert::exception([$this->managerUnknown, 'stop'], UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to restart the service via systemD
	 */
	public function testRestartSystemD(): void {
		$command = 'systemctl restart ' . self::SERVICE_NAME . '.service';
		$this->receiveCommand($command, true);
		Assert::noError([$this->managerSystemD, 'restart']);
	}

	/**
	 * Tests the function to restart the service via unknown init daemon
	 */
	public function testRestartUnknown(): void {
		Assert::exception([$this->managerUnknown, 'restart'], UnsupportedInitSystemException::class);
	}

	/**
	 * Tests the function to get status of the service via systemD
	 */
	public function testGetStatusSystemD(): void {
		$expected = 'status';
		$command = 'systemctl status ' . self::SERVICE_NAME . '.service';
		$this->receiveCommand($command, true, $expected);
		Assert::same($expected, $this->managerSystemD->getStatus());
	}

	/**
	 * Tests the function to get status of the service via unknown init daemon
	 */
	public function testGetStatusUnknown(): void {
		Assert::exception([$this->managerUnknown, 'getStatus'], UnsupportedInitSystemException::class);
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->managerSystemD = new ServiceManager('systemd', $this->commandManager);
		$this->managerUnknown = new ServiceManager('unknown', $this->commandManager);
	}

}

$test = new ServiceManagerTest();
$test->run();
