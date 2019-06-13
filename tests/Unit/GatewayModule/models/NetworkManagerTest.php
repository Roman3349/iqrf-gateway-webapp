<?php

/**
 * TEST: App\GatewayModule\Models\NetworkManager
 * @covers App\GatewayModule\Models\NetworkManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\GatewayModule\Models;

use App\GatewayModule\Models\NetworkManager;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Network manager
 */
class NetworkManagerTest extends CommandTestCase {

	/**
	 * @var NetworkManager Network manager with mocked command manager
	 */
	private $manager;

	/**
	 * @var string[] Mocked commands
	 */
	private $commands = [
		'hostname' => 'hostname -f',
		'ipAddressesEth0' => 'ip a s eth0 | grep inet | grep global | grep -v temporary | awk \'{print $2}\'',
		'ipAddressesWlan0' => 'ip a s wlan0 | grep inet | grep global | grep -v temporary | awk \'{print $2}\'',
		'macAddresses' => 'cat /sys/class/net/eth0/address',
		'networkAdapters' => 'ls /sys/class/net | awk \'{ print $0 }\'',
	];

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new NetworkManager($this->commandManager);
	}

	/**
	 * Tests the function to get hostname of the gateway
	 */
	public function testGetHostname(): void {
		$expected = 'gateway';
		$this->receiveCommand($this->commands['hostname'], null, $expected);
		Assert::same($expected, $this->manager->getHostname());
	}

	/**
	 * Tests the function to get IPv4 and IPv6 addresses of the gateway
	 */
	public function testGetIpAddresses(): void {
		$this->receiveCommand($this->commands['networkAdapters'], true, 'eth0' . PHP_EOL . 'wlan0' . PHP_EOL . 'lo');
		$this->receiveCommand($this->commands['ipAddressesEth0'], true, '192.168.1.100' . PHP_EOL . 'fda9:d95:d5b1::64');
		$this->receiveCommand($this->commands['ipAddressesWlan0'], true, '');
		$expected = ['eth0' => ['192.168.1.100', 'fda9:d95:d5b1::64']];
		Assert::same($expected, $this->manager->getIpAddresses());
	}

	/**
	 * Tests the function to get MAC addresses of the gateway
	 */
	public function testGetMacAddresses(): void {
		$this->receiveCommand($this->commands['networkAdapters'], true, 'eth0' . PHP_EOL . 'lo');
		$this->receiveCommand($this->commands['macAddresses'], true, '01:02:03:04:05:06');
		$expected = ['eth0' => '01:02:03:04:05:06'];
		Assert::same($expected, $this->manager->getMacAddresses());
	}

}

$test = new NetworkManagerTest();
$test->run();