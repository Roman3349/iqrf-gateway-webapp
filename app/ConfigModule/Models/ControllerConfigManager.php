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

namespace App\ConfigModule\Models;

use App\CoreModule\Models\JsonFileManager;

/**
 * IQRF Gateway Controller configuration manager
 */
class ControllerConfigManager {

	/**
	 * @var JsonFileManager $fileManager JSON file manager
	 */
	private $fileManager;

	/**
	 * JSON file containing Controller configuration
	 */
	private const FILE_NAME = 'config';

	/**
	 * Constructor
	 * @param JsonFileManager $fileManager JSON file manager
	 */
	public function __construct(JsonFileManager $fileManager) {
		$this->fileManager = $fileManager;
	}

	/**
	 * Read JSON configuration file and convert it to array
	 * @return array<string, array<array<string, bool|int|string>>|string>
	 */
	public function getConfig(): array {
		return $this->fileManager->read(self::FILE_NAME);
	}

	/**
	 * Saves the updated Controller configuration
	 * @param array<string, array<array<string, bool|int|string>>|string> $newConfig Controller configuration
	 */
	public function saveConfig(array $newConfig): void {
		$oldConfig = (array) $this->fileManager->read(self::FILE_NAME);
		$this->fileManager->write(self::FILE_NAME, array_merge($oldConfig, $newConfig));
	}

}
