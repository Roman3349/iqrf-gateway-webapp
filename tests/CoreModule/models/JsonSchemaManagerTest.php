<?php

/**
 * TEST: App\CoreModule\Models\JsonSchemaManager
 * @covers App\CoreModule\Models\JsonSchemaManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\CoreModule\Model;

use App\CoreModule\Exceptions\InvalidJsonException;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use App\CoreModule\Models\JsonFileManager;
use App\CoreModule\Models\JsonSchemaManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for JSON file manager
 */
class JsonSchemaManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var JsonFileManager JSON File manager
	 */
	private $fileManager;

	/**
	 * @var string Directory with configuration files
	 */
	private $filePath = __DIR__ . '/../../data/configuration/';

	/**
	 * @var JsonSchemaManager JSON schema manager
	 */
	private $manager;

	/**
	 * @var string Directory with JSON schemas
	 */
	private $schemaPath = __DIR__ . '/../../data/cfgSchemas/';

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Test function to set file name of JSON schema from component name (fail)
	 */
	public function testSetSchemaFromComponentFail(): void {
		Assert::exception(function () {
			$this->manager->setSchemaFromComponent('nonsense');
		}, NonExistingJsonSchemaException::class);
	}

	/**
	 * Test function to set file name of JSON schema from component name (success)
	 */
	public function testSetSchemaFromComponentSuccess(): void {
		Assert::noError(function () {
			$this->manager->setSchemaFromComponent('iqrf::MqttMessaging');
		});
	}

	/**
	 * Test function to validate JSON (invalid JSON)
	 */
	public function testValidateInvalid(): void {
		$this->manager->setSchemaFromComponent('iqrf::MqttMessaging');
		Assert::exception(function () {
			$json = (object)$this->fileManager->read('iqrf__MqMessaging');
			$this->manager->validate($json);
		}, InvalidJsonException::class);
	}

	/**
	 * Test function to validate JSON (valid JSON)
	 */
	public function testValidateValid(): void {
		$this->manager->setSchemaFromComponent('iqrf::MqttMessaging');
		$json = (object)$this->fileManager->read('iqrf__MqttMessaging');
		Assert::true($this->manager->validate($json));
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$this->fileManager = new JsonFileManager($this->filePath);
		$this->manager = new JsonSchemaManager($this->schemaPath);
	}

}

$test = new JsonSchemaManagerTest($container);
$test->run();