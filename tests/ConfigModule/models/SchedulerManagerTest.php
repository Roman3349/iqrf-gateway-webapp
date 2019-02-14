<?php

/**
 * TEST: App\ConfigModule\Models\SchedulerManager
 * @covers App\ConfigModule\Models\SchedulerManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ConfigModule\Models;

use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\MainManager;
use App\ConfigModule\Models\SchedulerManager;
use App\CoreModule\Models\JsonFileManager;
use App\CoreModule\Models\JsonSchemaManager;
use DateTime;
use Mockery;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for scheduler's task configuration manager
 */
class SchedulerManagerTest extends TestCase {

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManagerTemp;

	/**
	 * @var SchedulerManager Scheduler's task configuration manager
	 */
	private $manager;

	/**
	 * @var SchedulerManager Scheduler's task configuration manager (with temporary files)
	 */
	private $managerTemp;

	/**
	 * @var mixed[string] Scheduler's task settings
	 */
	private $array = [
		'taskId' => 1,
		'clientId' => 'SchedulerMessaging',
		'timeSpec' => [
			'cronTime' => '*/5 * 1 * * * *',
			'exactTime' => false,
			'periodic' => false,
			'period' => 0,
			'startTime' => '',
		],
		'task' => [
			'messaging' => 'WebsocketMessaging',
			'message' => [
				'mType' => 'iqrfRaw',
				'data' => [
					'msgId' => '1',
					'timeout' => 1000,
					'req' => ['rData' => '00.00.06.03.ff.ff'],
				],
				'returnVerbose' => true,
			],
		],
	];

	/**
	 * Test function to add configuration of Scheduler
	 */
	public function testAdd(): void {
		Environment::lock('config_scheduler', __DIR__ . '/../../temp/');
		$this->managerTemp->add('raw');
		$timestamp = (new DateTime())->getTimestamp();
		$expected = [
			'taskId' => $timestamp,
			'clientId' => '',
			'timeSpec' => [
				'cronTime' => [''],
				'exactTime' => false,
				'periodic' => false,
				'period' => 0,
				'startTime' => '',
			],
			'task' => [
				'messaging' => '',
				'message' => [
					'mType' => 'iqrfRaw',
					'data' => [
						'msgId' => '',
						'req' => ['rData' => ''],
					],
					'returnVerbose' => true,
				],
			],
		];
		Assert::equal($expected, $this->fileManagerTemp->read(strval($timestamp)));
	}

	/**
	 * Test function to delete configuration of Scheduler
	 */
	public function testDelete(): void {
		Environment::lock('config_scheduler', __DIR__ . '/../../temp/');
		$fileName = '1';
		$this->fileManagerTemp->write($fileName, $this->array);
		Assert::true($this->fileManagerTemp->exists($fileName));
		$this->managerTemp->delete(1);
		Assert::false($this->fileManagerTemp->exists($fileName));
	}

	/**
	 * Test function to fix HWPID format
	 */
	public function testFixHwpid(): void {
		Assert::equal('01.00', $this->manager->fixHwpid(1));
	}

	/**
	 * Test function to fix HWPID format (empty)
	 */
	public function testFixHwpidEmpty(): void {
		Assert::equal('00.00', $this->manager->fixHwpid());
	}

	/**
	 * Test function to get avaiable messagings
	 */
	public function testGetMessagings(): void {
		$expected = [
			'config.mq.title' => ['MqMessaging'],
			'config.mqtt.title' => ['MqttMessaging'],
			'config.websocket.title' => [
				'WebsocketMessaging',
				'WebsocketMessagingMobileApp',
				'WebsocketMessagingWebApp',
			],
		];
		Assert::same($expected, $this->manager->getMessagings());
	}

	/**
	 * Test function to get scheduler's services
	 */
	public function testGetServices(): void {
		$expected = ['SchedulerMessaging'];
		Assert::same($expected, $this->manager->getServices());
	}

	/**
	 * Test function to get tasks
	 */
	public function testList(): void {
		$expected = [
			[
				'time' => '*/5 * 1 * * * *',
				'service' => 'SchedulerMessaging',
				'messaging' => 'WebsocketMessaging',
				'mType' => 'iqrfRaw',
				'request' => '00.00.06.03.ff.ff',
				'id' => 1,
			], [
				'time' => '*/5 * 1 * * * *',
				'service' => 'SchedulerMessaging',
				'messaging' => 'WebsocketMessaging',
				'mType' => 'iqrfRawHdp',
				'request' => '00.00.06.03.ff.ff',
				'id' => 2,
			],
		];
		Assert::equal($expected, $this->manager->list());
	}

	/**
	 * Test function to load configuration of Scheduler
	 */
	public function testLoad(): void {
		Assert::equal($this->array, $this->manager->load(1));
		Assert::equal([], $this->manager->load(10));
	}

	/**
	 * Test function to save configuration of Scheduler
	 */
	public function testSave(): void {
		Environment::lock('config_scheduler', __DIR__ . '/../../temp/');
		$expected = $this->array;
		$expected['message']['nadr'] = '0';
		$this->managerTemp->save($expected);
		$expected['timeSpec']['cronTime'] = ['*/5', '*', '1', '*', '*', '*', '*'];
		Assert::equal($expected, $this->fileManagerTemp->read('1'));
	}

	/**
	 * Set up the test environment
	 */
	protected function setUp(): void {
		$configPath = __DIR__ . '/../../data/';
		$configTempPath = __DIR__ . '/../../temp/configuration/';
		$schemaPath = __DIR__ . '/../../data/cfgSchemas/';
		$this->fileManagerTemp = new JsonFileManager($configTempPath . 'scheduler/');
		$fileManager = new JsonFileManager($configPath);
		$schemaManager = new JsonSchemaManager($schemaPath);
		$genericConfigManager = new GenericManager($fileManager, $schemaManager);
		$mainConfigManager = Mockery::mock(MainManager::class);
		$mainConfigManager->shouldReceive('load')->andReturn(['cacheDir' => $configPath]);
		$mainConfigManagerTemp = Mockery::mock(MainManager::class);
		$mainConfigManagerTemp->shouldReceive('load')->andReturn(['cacheDir' => $configTempPath]);
		$this->manager = new SchedulerManager($mainConfigManager, $genericConfigManager);
		$this->managerTemp = new SchedulerManager($mainConfigManagerTemp, $genericConfigManager);
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

}

$test = new SchedulerManagerTest();
$test->run();
