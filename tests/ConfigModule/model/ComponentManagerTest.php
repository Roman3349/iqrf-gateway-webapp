<?php

/**
 * TEST: App\ConfigModule\Model\ComponentManager
 * @covers App\ConfigModule\Model\ComponentManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ConfigModule\Model;

use App\ConfigModule\Model\ComponentManager;
use App\CoreModule\Model\JsonFileManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for component configuration manager
 */
class ComponentManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManagerTemp;

	/**
	 * @var string File name (without .json)
	 */
	private $fileName = 'config';

	/**
	 * @var ComponentManager Componenct configuration manager
	 */
	private $manager;

	/**
	 * @var ComponentManager Component configuration manager
	 */
	private $managerTemp;

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp() {
		$configPath = __DIR__ . '/../../data/configuration/';
		$configTempPath = __DIR__ . '/../../temp/configuration/';
		$this->fileManager = new JsonFileManager($configPath);
		$this->fileManagerTemp = new JsonFileManager($configTempPath);
		$this->manager = new ComponentManager($this->fileManager);
		$this->managerTemp = new ComponentManager($this->fileManagerTemp);
	}

	/**
	 * The function to add a new component
	 */
	public function testAdd() {
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$array = [
			'name' => 'test::Test',
			'libraryPath' => '',
			'libraryName' => 'Test',
			'enabled' => false,
			'startlevel' => 100,
		];
		$expected['components'][] = $array;
		$this->managerTemp->add($array);
		Assert::same($expected['components'], $this->managerTemp->loadComponents());
	}

	/**
	 * Test function to delete the component
	 */
	public function testDelete() {
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		unset($expected['components'][30]);
		$this->managerTemp->delete(30);
		Assert::same($expected['components'], $this->managerTemp->loadComponents());
	}

	/**
	 * Test function to load configuration of components
	 */
	public function testLoadComponents() {
		$json = $this->fileManager->read($this->fileName);
		$expected = $json['components'];
		Assert::equal($expected, $this->manager->loadComponents());
	}

	/**
	 * Test function to load configuration of components
	 */
	public function testLoadComponent() {
		$json = $this->fileManager->read($this->fileName);
		$expected = $json['components'][0];
		Assert::equal($expected, $this->manager->loadComponent(0));
		Assert::equal([], $this->manager->loadComponent(100));
	}

	/**
	 * Test function to save configuration of components
	 */
	public function testSave() {
		$array = [
			'name' => 'iqrf::Scheduler',
			'libraryPath' => '',
			'libraryName' => 'Scheduler',
			'enabled' => false,
			'startlevel' => 1,
		];
		$expected = $this->fileManager->read($this->fileName);
		$this->fileManagerTemp->write($this->fileName, $expected);
		$expected['components'][6]['enabled'] = false;
		$this->managerTemp->save($array, 6);
		Assert::equal($expected, $this->fileManagerTemp->read($this->fileName));
	}

}

$test = new ComponentManagerTest($container);
$test->run();
