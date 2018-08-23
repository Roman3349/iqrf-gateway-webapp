<?php

/**
 * TEST: App\CloudModule\Model\AzureManager
 * @covers App\CloudModule\Model\AzureManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\ServiceModule\Model;

use App\CloudModule\Exception\InvalidConnectionStringException;
use App\CloudModule\Model\AzureManager;
use App\ConfigModule\Model\GenericManager;
use App\CoreModule\Model\JsonFileManager;
use App\CoreModule\Model\JsonSchemaManager;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Test for MS Azure IoT Hub manager
 */
class AzureManagerTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * @var AzureManager MS Azure IoT Hub manager
	 */
	private $manager;

	/**
	 * @var \Mockery\Mock Mocked MS Azure IoT hub manager
	 */
	private $mockedManager;

	/**
	 * @var string MS Azure IoT Hub connection string for the device
	 */
	private $connectionString = 'HostName=iqrf.azure-devices.net;DeviceId=IQRFGW;SharedAccessKey=1234567890abcdefghijklmnopqrstuvwxyzABCDEFG=';

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
		$configPath = __DIR__ . '/../../temp/configuration/';
		$schemaPath = __DIR__ . '/../../data/cfgSchemas/';
		$this->fileManager = new JsonFileManager($configPath);
		$schemaManager = new JsonSchemaManager($schemaPath);
		$configManager = new GenericManager($this->fileManager, $schemaManager);
		$this->manager = new AzureManager($configManager);
		$this->mockedManager = \Mockery::mock(AzureManager::class, [$configManager])->makePartial();
		$this->mockedManager->shouldReceive('generateSasToken')->andReturn('generatedSasToken');
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown() {
		\Mockery::close();
	}

	/**
	 * Test function to create MQTT interface
	 */
	public function testCreateMqttInterface() {
		$mqtt = [
			'component' => 'iqrf::MqttMessaging',
			'instance' => 'MqttMessagingAzure',
			'BrokerAddr' => 'ssl://iqrf.azure-devices.net:8883',
			'ClientId' => 'IQRFGW',
			'Persistence' => 1,
			'Qos' => 0,
			'TopicRequest' => 'devices/IQRFGW/messages/devicebound/#',
			'TopicResponse' => 'devices/IQRFGW/messages/events/',
			'User' => 'iqrf.azure-devices.net/IQRFGW',
			'Password' => 'generatedSasToken',
			'EnabledSSL' => true,
			'KeepAliveInterval' => 20,
			'ConnectTimeout' => 5,
			'MinReconnect' => 1,
			'MaxReconnect' => 64,
			'TrustStore' => '',
			'KeyStore' => '',
			'PrivateKey' => '',
			'PrivateKeyPassword' => '',
			'EnabledCipherSuites' => '',
			'EnableServerCertAuth' => false,
			'acceptAsyncMsg' => false,
		];
		$array['ConnectionString'] = $this->connectionString;
		$this->mockedManager->createMqttInterface($array);
		Assert::same($mqtt, $this->fileManager->read('MqttMessagingAzure'));
	}

	/**
	 * Test function to check the connection string (invalid connection string)
	 */
	public function testCheckConnectionStringInvalid() {
		$invalidString = 'HostName=iqrf.azure-devices.net;SharedAccessKeyName=iothubowner;SharedAccessKey=1234567890abcdefghijklmnopqrstuvwxyzABCDEFG=';
		Assert::exception(function() use ($invalidString) {
			$this->mockedManager->checkConnectionString($invalidString);
		}, InvalidConnectionStringException::class);
	}

	/**
	 * Test function to check the connection string (valid connection string)
	 */
	public function testCheckConnectionStringValid() {
		Assert::null($this->mockedManager->checkConnectionString($this->connectionString));
	}

	/**
	 * Test function to generate shared access signature token
	 */
	public function testGenerateSasToken() {
		$resourceUri = 'iqrf.azure-devices.net/devices/iqrfGwTest';
		$signingKey = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFG';
		$policyName = null;
		$expiresInMins = intdiv((new \DateTime('2018-05-10T11:00:00'))->getTimestamp(), 60) -
				intdiv((new \DateTime())->getTimestamp(), 60) + 5256000;
		$actual = $this->manager->generateSasToken($resourceUri, $signingKey, $policyName, $expiresInMins);
		$expected = 'SharedAccessSignature sr=iqrf.azure-devices.net%2Fdevices%2FiqrfGwTest&sig=loSMVo4aSTBFh6psEwJcSInBGo%2BSD3noiFSHbgQuSMo%3D&se=1841302800';
		Assert::same($expected, $actual);
	}

	/**
	 * Test function to parse the connection string
	 */
	public function testParseConnectionString() {
		$expected = [
			'HostName' => 'iqrf.azure-devices.net',
			'DeviceId' => 'IQRFGW',
			'SharedAccessKey' => '1234567890abcdefghijklmnopqrstuvwxyzABCDEFG',
		];
		Assert::same($expected, $this->mockedManager->parseConnectionString($this->connectionString));
	}

}

$test = new AzureManagerTest($container);
$test->run();
