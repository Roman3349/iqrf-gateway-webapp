<?php

/**
 * TEST: App\Model\GwInfoManager
 * @phpVersion >= 5.6
 * @testCase
 */
use App\Model\GwInfoManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../bootstrap.php';

class GwInfoManagerTest extends TestCase {

	private $container;

	function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @test
	 * Test function to get IPv4 and IPv6 addresses of the gateway
	 */
	public function testGetIpAddresses() {
		$commandManager = \Mockery::mock('App\Model\CommandManager');
		$output = '192.168.1.100 fda9:d95:d5b1::64';
		$commandManager->shouldReceive('send')->with('hostname -I')->andReturn($output);
		$gwInfoManager = new GwInfoManager($commandManager);
		Assert::same(['192.168.1.100', 'fda9:d95:d5b1::64'], $gwInfoManager->getIpAddresses());
	}

	/**
	 * @test
	 * Test function to get MAC addresses of the gateway
	 */
	public function testGetMacAddresses() {
		$commandManager = \Mockery::mock('App\Model\CommandManager');
		$output = '01:02:03:04:05:06' . PHP_EOL . '00:00:00:00:00:00';
		$commandManager->shouldReceive('send')->with('cat /sys/class/net/*/address')->andReturn($output);
		$gwInfoManager = new GwInfoManager($commandManager);
		Assert::same(['01:02:03:04:05:06', '00:00:00:00:00:00'], $gwInfoManager->getMacAddresses());
	}

	/**
	 * @test
	 * Test function to get hostname of the gateway
	 */
	public function testGetHostname() {
		$commandManager = \Mockery::mock('App\Model\CommandManager');
		$output = 'gateway';
		$commandManager->shouldReceive('send')->with('hostname -f')->andReturn($output);
		$gwInfoManager = new GwInfoManager($commandManager);
		Assert::same($output, $gwInfoManager->getHostname());
	}

}

$test = new GwInfoManagerTest($container);
$test->run();