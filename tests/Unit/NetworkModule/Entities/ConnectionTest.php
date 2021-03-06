<?php

/**
 * TEST: App\NetworkModule\Entities\Connection
 * @covers App\NetworkModule\Entities\Connection
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\NetworkModule\Entities;

use App\NetworkModule\Entities\Connection;
use App\NetworkModule\Enums\ConnectionTypes;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for network connection entity
 */
final class ConnectionTest extends TestCase {

	/**
	 * Network connection name
	 */
	private const NAME = 'eth0';

	/**
	 * @var UuidInterface Network connection UUID
	 */
	private $uuid;

	/**
	 * @var ConnectionTypes Network connection type
	 */
	private $type;

	/**
	 * Network interface name
	 */
	private const INTERFACE = 'eth0';

	/**
	 * @var Connection Network connection entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	public function __construct() {
		$this->uuid = Uuid::fromString('25ab1b06-2a86-40a9-950f-1c576ddcd35a');
		$this->type = ConnectionTypes::ETHERNET();
		$this->entity = new Connection(self::NAME, $this->uuid, $this->type, self::INTERFACE);
	}

	/**
	 * Tests the function to deserialize network connection entity from nmcli row
	 */
	public function testNmCliDeserialize(): void {
		$string = 'eth0:25ab1b06-2a86-40a9-950f-1c576ddcd35a:802-3-ethernet:eth0';
		Assert::equal($this->entity, Connection::nmCliDeserialize($string));
	}

	/**
	 * Tests the function to get network connection name
	 */
	public function testGetName(): void {
		Assert::same(self::NAME, $this->entity->getName());
	}

	/**
	 * Tests the function to get network connection UUID
	 */
	public function testGetUuid(): void {
		Assert::same($this->uuid, $this->entity->getUuid());
	}

	/**
	 * Tests the function to get network connection type
	 */
	public function testGetType(): void {
		Assert::same($this->type, $this->entity->getType());
	}

	/**
	 * Tests the function to get network interface name
	 */
	public function testGetInterfaceName(): void {
		Assert::same(self::INTERFACE, $this->entity->getInterfaceName());
	}

	/**
	 * Tests the function to serialize network connection entity into JSON
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'name' => self::NAME,
			'uuid' => $this->uuid->toString(),
			'type' => $this->type->toScalar(),
			'interfaceName' => self::INTERFACE,
		];
		Assert::same($expected, $this->entity->jsonSerialize());
	}

}

$test = new ConnectionTest();
$test->run();
