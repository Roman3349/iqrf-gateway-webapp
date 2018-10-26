<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace App\GatewayModule\Models;

use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\JsonFileManager;
use App\CoreModule\Models\VersionManager;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Models\DpaRawManager;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * Tool for getting information about this gateway
 */
class InfoManager {

	use SmartObject;

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var DpaRawManager DPA Raw request and response manager
	 */
	private $dpaManager;

	/**
	 * @var JsonFileManager JSON file manager
	 */
	private $jsonFileManager;

	/**
	 * @var VersionManager version manager
	 */
	private $versionManager;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param DpaRawManager $dpaManager DPA Raw request and response manager
	 * @param VersionManager $versionManager Version manager
	 */
	public function __construct(CommandManager $commandManager, DpaRawManager $dpaManager, VersionManager $versionManager) {
		$this->commandManager = $commandManager;
		$this->dpaManager = $dpaManager;
		$this->jsonFileManager = new JsonFileManager(__DIR__ . '/../../../');
		$this->versionManager = $versionManager;
	}

	/**
	 * Get board's vendor, name and version
	 * @return string Board's vendor, name and version
	 */
	public function getBoard(): string {
		$deviceTree = $this->commandManager->send('cat /proc/device-tree/model', true);
		if ($deviceTree !== '') {
			return $deviceTree;
		}
		$dmiBoardVendor = $this->commandManager->send('cat /sys/class/dmi/id/board_vendor', true);
		$dmiBoardName = $this->commandManager->send('cat /sys/class/dmi/id/board_name', true);
		$dmiBoardVersion = $this->commandManager->send('cat /sys/class/dmi/id/board_version', true);
		if ($dmiBoardName !== '' && $dmiBoardVendor !== '' && $dmiBoardVersion !== '') {
			return $dmiBoardVendor . ' ' . $dmiBoardName . ' (' . $dmiBoardVersion . ')';
		}
		return 'UNKNOWN';
	}

	/**
	 * Get IPv4 and IPv6 addresses of the gateway
	 * @return array IPv4 and IPv6 addresses
	 */
	public function getIpAddresses(): array {
		$addresses = [];
		$lsInterfaces = $this->commandManager->send('ls /sys/class/net | awk \'{ print $0 }\'', true);
		$interfaces = explode(PHP_EOL, $lsInterfaces);
		foreach ($interfaces as $interface) {
			if ($interface === 'lo') {
				continue;
			}
			$cmd = 'ip a s ' . $interface . ' | grep inet | grep global | grep -v temporary | awk \'{print $2}\'';
			$output = $this->commandManager->send($cmd, true);
			if ($output !== '') {
				$addresses[$interface] = explode(PHP_EOL, $output);
			}
		}
		return $addresses;
	}

	/**
	 * Get MAC addresses of the gateway
	 * @return array MAC addresses array
	 */
	public function getMacAddresses(): array {
		$addresses = [];
		$lsInterfaces = $this->commandManager->send('ls /sys/class/net | awk \'{ print $0 }\'', true);
		$interfaces = explode(PHP_EOL, $lsInterfaces);
		foreach ($interfaces as $interface) {
			if ($interface === 'lo') {
				continue;
			}
			$cmd = 'cat /sys/class/net/' . $interface . '/address';
			$addresses[$interface] = $this->commandManager->send($cmd, true);
		}
		return $addresses;
	}

	/**
	 * Get version of the daemon
	 * @return string IQRF Daemon version
	 */
	public function getDaemonVersion(): string {
		$cmd = 'iqrfgd2 version';
		$daemonExistence = $this->commandManager->commandExist('iqrfgd2');
		if (!$daemonExistence) {
			return 'none';
		}
		$result = $this->commandManager->send($cmd);
		if ($result !== '') {
			return $result;
		}
		return 'unknown';
	}

	/**
	 * Get hostname of the gateway
	 * @return string Hostname
	 */
	public function getHostname(): string {
		$cmd = 'hostname -f';
		return $this->commandManager->send($cmd);
	}

	/**
	 * Get information about the Coordinator
	 * @return array Information about the Coordinator
	 * @throws DpaErrorException
	 * @throws EmptyResponseException
	 * @throws JsonException
	 * @throws UserErrorException
	 */
	public function getCoordinatorInfo(): array {
		$response = $this->dpaManager->send('00.00.02.00.FF.FF');
		return $this->dpaManager->parseResponse($response);
	}

	/**
	 * Get current version of this wab application
	 * @return string Version of this web application
	 * @throws JsonException
	 */
	public function getWebAppVersion(): string {
		return $this->versionManager->getInstalledWebapp();
	}

}