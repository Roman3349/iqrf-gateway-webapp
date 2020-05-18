<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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

namespace App\IqrfNetModule\Models;

use App\IqrfNetModule\Enums\TrSeries;
use Iqrf\Repository\Entities\OsDpa;
use Iqrf\Repository\Models\FilesManager;
use Iqrf\Repository\Models\OsAndDpaManager;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;

/**
 * DPA manager
 */
class DpaManager {

	/**
	 * @var FilesManager Files manager
	 */
	private $filesManager;

	/**
	 * @var OsAndDpaManager IQRF Repository OS&DPA manager
	 */
	private $osDpaManager;

	/**
	 * Constructor
	 * @param OsAndDpaManager $osDpaManager IQRF Repository OS&DPA manager
	 * @param FilesManager $filesManager Files manager
	 */
	public function __construct(OsAndDpaManager $osDpaManager, FilesManager $filesManager) {
		$this->osDpaManager = $osDpaManager;
		$this->filesManager = $filesManager;
	}

	/**
	 * Lists DPA versions for IQRF OS build
	 * @param string $osBuild IQRF OS build
	 * @return OsDpa[] IQRF OS and DPA versions
	 */
	public function list(string $osBuild): array {
		return $this->osDpaManager->get($osBuild);
	}

	/**
	 * Returns DPA file name
	 * @param string $osBuild IQRF OS build number
	 * @param string $dpaVersion DPA version
	 * @param TrSeries $trSeries TR series
	 * @param string|null $rfMode RF mode
	 * @return string|null DPA file name
	 */
	public function getFile(string $osBuild, string $dpaVersion, TrSeries $trSeries, ?string $rfMode = null): ?string {
		$path = $this->osDpaManager->get($osBuild, $dpaVersion)[0]->getDownloadPath();
		$this->filesManager->setPath($path);
		$files = $this->filesManager->list()->getFiles();
		$filePrefixes = [
			'GeneralHWP-Coordinator-' . $rfMode . '-SPI-' . $trSeries->toScalar() . '-',
			'HWP-Coordinator-' . $rfMode . '-SPI-' . $trSeries->toScalar() . '-',
			'DPA-Coordinator-SPI-' . $trSeries->toScalar() . '-',
		];
		foreach ($files as $file) {
			foreach ($filePrefixes as $filePrefix) {
				if (Strings::startsWith($file->getName(), $filePrefix)) {
					$fileContent = $this->filesManager->download($file->getName());
					$filePath = '/tmp/iqrf-gateway-daemon/' . $file->getName();
					FileSystem::write($filePath, $fileContent);
					return $filePath;
				}
			}
		}
		return null;
	}

}
