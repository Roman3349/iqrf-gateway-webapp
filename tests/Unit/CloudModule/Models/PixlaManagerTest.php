<?php
/**
 * TEST: App\CloudModule\Models\PixlaManager
 * @covers App\CloudModule\Models\PixlaManager
 * @phpVersion >= 7.2
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\CloudModule\Models;

use App\CloudModule\Models\PixlaManager;
use App\CoreModule\Models\FileManager;
use Mockery;
use Mockery\MockInterface;
use Nette\IOException;
use Tester\Assert;
use Tests\Toolkit\TestCases\CommandTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for PIXLA management system manager
 */
final class PixlaManagerTest extends CommandTestCase {

	/**
	 * File containing PIXLA token
	 */
	private const FILE_NAME = 'customer_id';

	/**
	 * PIXLA token
	 */
	private const TOKEN = 'pixla-token';

	/**
	 * PIXLA new token
	 */
	private const NEW_TOKEN = 'pixla-new-token';

	/**
	 * @var FileManager|MockInterface File manager
	 */
	private $fileManager;

	/**
	 * @var PixlaManager PIXLA management system manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->fileManager = Mockery::mock(FileManager::class);
		$this->manager = new PixlaManager($this->fileManager);
	}

	/**
	 * Tests the function to get PIXLA token (success)
	 */
	public function testGetTokenSuccess(): void {
		$this->fileManager->shouldReceive('read')
			->withArgs([self::FILE_NAME])
			->andReturn(self::TOKEN);
		Assert::same(self::TOKEN, $this->manager->getToken());
	}

	/**
	 * Tests the function to get PIXLA token (failure)
	 */
	public function testGetTokenFailure(): void {
		$this->fileManager->shouldReceive('read')
			->withArgs([self::FILE_NAME])
			->andThrow(IOException::class);
		Assert::null($this->manager->getToken());
	}

	/**
	 * Tests the function to set PIXLA token (success)
	 */
	public function testSetTokenSuccess(): void {
		$this->fileManager->shouldReceive('write')
			->withArgs([self::FILE_NAME, self::NEW_TOKEN]);
		Assert::noError(function (): void {
			$this->manager->setToken(self::NEW_TOKEN);
		});
	}

	/**
	 * Tests the function to set PIXLa token (failure)
	 */
	public function testSetTokenFailure(): void {
		$this->fileManager->shouldReceive('write')
			->withArgs([self::FILE_NAME, self::NEW_TOKEN])
			->andThrow(IOException::class);
		Assert::exception(function (): void {
			$this->manager->setToken(self::NEW_TOKEN);
		}, IOException::class);
	}

}

$test = new PixlaManagerTest();
$test->run();
