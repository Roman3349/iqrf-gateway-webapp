<?php

/**
 * TEST: App\NetworkModule\Entities\IPv6Connection
 * @covers App\NetworkModule\Entities\IPv6Connection
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\IPv6Address;
use App\NetworkModule\Entities\IPv6Connection;
use App\NetworkModule\Enums\IPv6Methods;
use Darsyn\IP\Version\IPv6;
use Nette\Utils\ArrayHash;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connection entity
 */
class IPv6ConnectionTest extends TestCase {

	/**
	 * @var IPv6Methods IPv6 connection method
	 */
	private $method;

	/**
	 * @var IPv6Address[] IPv6 addresses
	 */
	private $addresses;

	/**
	 * @var IPv6[] IPv6 addresses of DNS servers
	 */
	private $dns;

	/**
	 * @var IPv6Connection IPv6 connection entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->method = IPv6Methods::MANUAL();
		$this->addresses = [
			new IPv6Address(IPv6::factory('2001:470:5bb2::2'), 64, IPv6::factory('fe80::1')),
		];
		$this->dns = [
			IPv6::factory('2001:470:5bb2::1'),
		];
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void  {
		$this->entity = new IPv6Connection($this->method, $this->addresses, $this->dns);
	}

	/**
	 * Tests the function to set values from the network connection configuration form
	 */
	public function testFromForm(): void {
		$arrayHash = ArrayHash::from([
			'method' => 'manual',
			'addresses' => [
				[
					'address' => '2001:470:5bb2::50c',
					'prefix' => 64,
					'gateway' => 'fe80::6f0:21ff:fe23:2900',
				],
			],
			'dns' => [['address' => 'fd50:ccd6:13ed::1']],
		], true);
		$addresses = [
			new IPv6Address(IPv6::factory('2001:470:5bb2::50c'), 64, IPv6::factory('fe80::6f0:21ff:fe23:2900')),
		];
		$dns = [IPv6::factory('fd50:ccd6:13ed::1')];
		$expected = new IPv6Connection($this->method, $addresses, $dns);
		$this->entity->fromForm($arrayHash);
		Assert::equal($expected, $this->entity);
	}

	/**
	 * Tests the function to create a new IPv6 connection entity from nmcli connection configuration
	 */
	public function testFromNmCli(): void {
		$configuration = FileSystem::read(__DIR__ . '/../../../data/eth0.conf');
		Assert::equal($this->entity, IPv6Connection::fromNmCli($configuration));
	}

	/**
	 * Tests the function to get IPv6 connection method
	 */
	public function testGetMethod(): void {
		Assert::same($this->method, $this->entity->getMethod());
	}

	/**
	 * Tests the function to get IPv6 addresses
	 */
	public function testGetAddresses(): void {
		Assert::same($this->addresses, $this->entity->getAddresses());
	}

	/**
	 * Tests the function to get IPv6 addresses of DNS servers
	 */
	public function testGetDns(): void {
		Assert::same($this->dns, $this->entity->getDns());
	}

	/**
	 * Tests the function to convert the IPv6 connection entity to an array for the form
	 */
	public function testToForm(): void {
		$expected = [
			'method' => 'manual',
			'addresses' => [
				[
					'address' => '2001:470:5bb2::2',
					'prefix' => 64,
					'gateway' => 'fe80::1',
				],
			],
			'dns' => [['address' => '2001:470:5bb2::1']],
		];
		Assert::same($expected, $this->entity->toForm());
	}

	/**
	 * Tests the function to convert IPv6 connection entity to nmcli configuration string
	 */
	public function testToNmCli(): void {
		$expected = 'ipv6.method "manual" ipv6.addresses "2001:470:5bb2::2/64" ipv6.gateway "fe80::1" ipv6.dns "2001:470:5bb2::1" ';
		Assert::same($expected, $this->entity->toNmCli());
	}

}

$test = new IPv6ConnectionTest();
$test->run();