<?php
/**
 * TEST: App\IqrfNetModule\Models\SecurityManager
 * @covers App\IqrfNetModule\Models\SecurityManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Models;

use App\IqrfNetModule\Enums\DataFormat;
use App\IqrfNetModule\Models\SecurityManager;
use Tests\Toolkit\TestCases\WebSocketTestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Test for IQMESH Security manager
 */
class SecurityManagerTest extends WebSocketTestCase {

	/**
	 * @var int Network device address
	 */
	private $address = 0;

	/**
	 * @var SecurityManager IQMESH Security manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->manager = new SecurityManager($this->request, $this->wsClient);
	}

	/**
	 * Tests the function to set an access password in HEX format
	 */
	public function testSetAccessPassword(): void {
		$request = [
			'mType' => 'iqrfEmbedOs_SetSecurity',
			'data' => [
				'req' => [
					'nAdr' => $this->address,
					'param' => [
						'type' => 0,
						'data' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
					],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->setAccessPassword($this->address, '000102030405060708090A0B0C0D0E0F', DataFormat::HEX);
		});
	}

	/**
	 * Tests the function to set an user key in ASCII format
	 */
	public function testSetUserKey(): void {
		$request = [
			'mType' => 'iqrfEmbedOs_SetSecurity',
			'data' => [
				'req' => [
					'nAdr' => $this->address,
					'param' => [
						'type' => 1,
						'data' => [48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 65, 66, 67, 68, 69, 70],
					],
				],
				'returnVerbose' => true,
			],
		];
		$this->assertRequest($request, function (): void {
			$this->manager->setUserKey($this->address, '0123456789ABCDEF', DataFormat::ASCII);
		});
	}

}

$test = new SecurityManagerTest();
$test->run();