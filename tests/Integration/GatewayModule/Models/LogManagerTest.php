<?php

/**
 * TEST: App\GatewayModule\Models\LogManager
 * @covers App\GatewayModule\Models\LogManager
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\GatewayModule\Models;

use App\CoreModule\Entities\CommandStack;
use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FileManager;
use App\CoreModule\Models\ZipArchiveManager;
use App\GatewayModule\Models\LogManager;
use DateTime;
use Nette\Application\Responses\FileResponse;
use Tester\Assert;
use Tester\TestCase;
use ZipArchive;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for Gateway info manager
 */
class LogManagerTest extends TestCase {

	/**
	 * @var FileManager Text file manager
	 */
	private $fileManager;

	/**
	 * @var LogManager IQRF Gateway Daemon's log manager
	 */
	private $manager;

	/**
	 * Tests the function to load the latest IQRF Gateway Daemon's log
	 */
	public function testLoad(): void {
		$fileName = '2018-08-13-13-37-834-iqrf-gateway-daemon.log';
		$expected = $this->fileManager->read($fileName);
		Assert::same($expected, $this->manager->load());
	}

	/**
	 * Tests the function to download a ZIP archive with IQRF Gateway Daemon's logs
	 */
	public function testDownload(): void {
		$actual = $this->manager->download();
		$path = '/tmp/iqrf-daemon-gateway-logs.zip';
		$fileName = 'iqrf-gateway-daemon-logs' . (new DateTime())->format('c') . '.zip';
		$contentType = 'application/zip';
		$expected = new FileResponse($path, $fileName, $contentType, true);
		Assert::equal($expected, $actual);
		$zipManager = new ZipArchiveManager($path, ZipArchive::CREATE);
		$logs = [
			'2018-08-13-13-37-834-iqrf-gateway-daemon.log',
			'2018-08-13-13-37-496-iqrf-gateway-daemon.log',
		];
		foreach ($logs as $log) {
			$expectedLog = $this->fileManager->read($log);
			Assert::same($expectedLog, $zipManager->openFile($log));
		}
	}

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$logDir = realpath(__DIR__ . '/../../../data/logs/');
		$commandStack = new CommandStack();
		$commandManager = new CommandManager(false, $commandStack);
		$this->fileManager = new FileManager($logDir, $commandManager);
		$this->manager = new LogManager($logDir);
		$modifyDates = [
			'2018-08-13T13:37:13.107090' => '2018-08-13-13-37-496-iqrf-gateway-daemon.log',
			'2018-08-13T13:37:18.262028' => '2018-08-13-13-37-834-iqrf-gateway-daemon.log',
		];
		foreach ($modifyDates as $date => $fileName) {
			$commandManager->run('touch -m -d ' . $date . ' ' . $logDir . '/' . $fileName);
		}
	}

}

$test = new LogManagerTest();
$test->run();
