<?php

/**
 * TEST: App\CoreModule\Model\VersionManager
 * @covers App\CoreModule\Model\VersionManager
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\CoreModule\Model;

use App\CoreModule\Model\CommandManager;
use App\CoreModule\Model\VersionManager;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Nette\Caching\Storages\DevNullStorage;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for version manager
 */
class VersionManagerTest extends TestCase {

	/**
	 * @var DevNullStorage Cache storage for testing
	 */
	private $cacheStorage;

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var \Mockery\MockInterface Mocked command manager
	 */
	private $commandManager;

	/**
	 * @var \Mockery\MockInterface Mocked HTTP client
	 */
	private $client;

	/**
	 * @var string Current version of the webapp
	 */
	private $currentVersion = '2.0.0-beta';

	/**
	 * @var string Current stable version of the webapp
	 */
	private $stableVersion = '1.1.6';

	/**
	 * @var VersionManager Version manager
	 */
	private $manager;

	/**
	 * @var \Mockery\Mock Mocked version manager
	 */
	private $managerMocked;

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
	protected function setUp(): void {
		$this->commandManager = \Mockery::mock(CommandManager::class);
		$this->cacheStorage = new DevNullStorage();
		$client = new Client();
		$this->manager = new VersionManager($this->commandManager, $this->cacheStorage, $client);
		$this->managerMocked = \Mockery::mock(VersionManager::class)->makePartial();
	}

	/**
	 * Cleanup the test environment
	 */
	protected function tearDown(): void {
		\Mockery::close();
	}

	/**
	 * Test function to check if an update is available for the webapp
	 */
	public function testAvailableWebappUpdateNo(): void {
		$this->managerMocked->shouldReceive('getInstalledWebapp')
				->with(false)->andReturn($this->currentVersion);
		$this->managerMocked->shouldReceive('getCurrentWebapp')
				->with()->andReturn($this->currentVersion);
		Assert::false($this->managerMocked->availableWebappUpdate());
	}

	/**
	 * Test function to check if an update is available for the webapp
	 */
	public function testAvailableWebappUpdateYes(): void {
		$this->managerMocked->shouldReceive('getInstalledWebapp')
				->with(false)->andReturn($this->stableVersion);
		$this->managerMocked->shouldReceive('getCurrentWebapp')
				->with()->andReturn($this->currentVersion);
		Assert::true($this->managerMocked->availableWebappUpdate());
	}

	/**
	 * Test function to get the current webapp version (stable - success)
	 */
	public function testGetCurrentWebappStable(): void {
		$responseMock = new MockHandler([
			new Response(200, [], '{"version": "' . $this->currentVersion . '"}'),
		]);
		$handler = HandlerStack::create($responseMock);
		$client = new Client(['handler' => $handler]);
		$manager = new VersionManager($this->commandManager, $this->cacheStorage, $client);
		Assert::same($this->currentVersion, $manager->getCurrentWebapp());
	}

	/**
	 * Test function to get the current webapp version (stable - failure, master - success)
	 */
	public function testGetCurrentWebappMaster(): void {
		$responseMock = new MockHandler([
			new Response(404),
			new Response(200, [], '{"version": "' . $this->currentVersion . '"}'),
		]);
		$handler = HandlerStack::create($responseMock);
		$client = new Client(['handler' => $handler]);
		$manager = new VersionManager($this->commandManager, $this->cacheStorage, $client);
		Assert::same($this->currentVersion, $manager->getCurrentWebapp());
	}

	/**
	 * Test function to get the installed webapp version
	 */
	public function testGetInstalledWebapp(): void {
		Assert::same($this->currentVersion, $this->manager->getInstalledWebapp(false));
	}

	/**
	 * Test function to get the installed webapp version with git
	 */
	public function testGetInstalledWebappGit(): void {
		$this->commandManager->shouldReceive('send')
				->with('git rev-parse --is-inside-work-tree')->andReturn('true');
		$this->commandManager->shouldReceive('send')
				->with('git rev-parse --verify HEAD')->andReturn('commit');
		$expected = 'v' . $this->currentVersion . ' (commit)';
		Assert::same($expected, $this->manager->getInstalledWebapp());
	}

	/**
	 * Test function to get the installed webapp version
	 */
	public function testGetInstalledWebappFallback(): void {
		$this->commandManager->shouldReceive('send')
				->with('git rev-parse --is-inside-work-tree')->andReturn('false');
		$expected = 'v' . $this->currentVersion;
		Assert::same($expected, $this->manager->getInstalledWebapp());
	}

}

$test = new VersionManagerTest($container);
$test->run();