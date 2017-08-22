<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\ConfigModule\Model;

use App\Model\JsonFileManager;
use Nette;
use Nette\Utils\ArrayHash;

class ComponentManager {

	use Nette\SmartObject;

	/**
	 * @var JsonFileManager
	 */
	private $fileManager;

	/**
	 * @var string
	 */
	private $fileName = 'config';

	/**
	 * Constructor
	 * @param JsonFileManager $fileManager
	 */
	public function __construct(JsonFileManager $fileManager) {
		$this->fileManager = $fileManager;
	}

	/**
	 * Convert Main configuration form array to JSON array
	 * @return array Array for form
	 */
	public function load() {
		$json = $this->fileManager->read($this->fileName);
		return $json['Components'];
	}

	/**
	 * Save components setting
	 * @param ArrayHash $components Components settings
	 */
	public function save(ArrayHash $components) {
		$json = $this->fileManager->read($this->fileName);
		$json['Components'] = $this->saveJson($components);
		$this->fileManager->write($this->fileName, $json);
	}

	/**
	 * Convert array from Components configuration form to JSON
	 * @param ArrayHash $components Components configuration form array
	 * @return array JSON array
	 */
	public function saveJson(ArrayHash $components) {
		$array = [];
		foreach ($components as $component => $enabled) {
			array_push($array, ['ComponentName' => $component, 'Enabled' => $enabled]);
		}
		return $array;
	}

}
