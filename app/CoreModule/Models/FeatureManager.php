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

namespace App\CoreModule\Models;

use App\CoreModule\Exceptions\FeatureNotFoundException;
use Nette\IOException;
use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon;
use Nette\Utils\FileSystem;

/**
 * Optional feature manager
 */
class FeatureManager {

	/**
	 * @var string Path to the configuration file
	 */
	private $path;

	/**
	 * Default configuration
	 */
	private const DEFAULTS = [
		'docs' => [
			'enabled' => true,
			'url' => 'https://docs.iqrf.org/iqrf-gateway/',
		],
		'grafana' => [
			'enabled' => false,
			'url' => '/grafana/',
		],
		'networkManager' => [
			'enabled' => false,
		],
		'nodeRed' => [
			'enabled' => false,
			'url' => '/node-red/',
		],
		'pixla' => [
			'enabled' => false,
		],
		'ssh' => [
			'enabled' => false,
		],
		'supervisord' => [
			'enabled' => false,
			'url' => 'supervisord/',
		],
		'trUpload' => [
			'enabled' => false,
		],
		'updater' => [
			'enabled' => false,
		],
		'unattendedUpgrades' => [
			'enabled' => false,
		],
		'versionChecker' => [
			'enabled' => false,
		],
	];

	/**
	 * Constructor
	 * @param string $path Path to the configuration file
	 */
	public function __construct(string $path) {
		$this->path = $path;
	}

	/**
	 * Reads features configuration
	 * @return array<string, array<string, bool|int|string>> Features configuration
	 */
	protected function read(): array {
		try {
			$content = FileSystem::read($this->path);
			$configuration = Neon::decode($content) ?? [];
			return array_merge(self::DEFAULTS, $configuration);
		} catch (IOException | NeonException $e) {
			return self::DEFAULTS;
		}
	}

	/**
	 * Writes the features configuration
	 * @param array<string, array<string, bool|int|string>> $features Feature configuration to write
	 * @throws IOException
	 */
	protected function write(array $features): void {
		$content = Neon::encode($features, Neon::BLOCK);
		FileSystem::write($this->path, $content);
	}

	/**
	 * Checks if the feature is enabled
	 * @param string $name Feature name
	 * @return bool Is the feature enabled?
	 * @throws FeatureNotFoundException
	 */
	public function isEnabled(string $name): bool {
		if (!array_key_exists($name, self::DEFAULTS)) {
			throw new FeatureNotFoundException();
		}
		$configuration = $this->read();
		$feature = $configuration[$name] ?? self::DEFAULTS[$name];
		return $feature['enabled'] ?? self::DEFAULTS[$name]['enabled'];
	}

	/**
	 * Checks if the feature has URL
	 * @param string $name Feature name
	 * @return bool Has the feature URL?
	 */
	public function hasUrl(string $name): bool {
		if (!array_key_exists($name, self::DEFAULTS)) {
			throw new FeatureNotFoundException();
		}
		$configuration = $this->read();
		$feature = $configuration[$name] ?? self::DEFAULTS[$name];
		return isset($feature['url']);
	}

	/**
	 * Lists enabled features
	 * @return array<string> Enabled features
	 */
	public function listEnabled(): array {
		$features = $this->read();
		$enabled = [];
		foreach ($features as $feature => $configuration) {
			if (!$configuration['enabled'] ?? true) {
				continue;
			}
			$enabled[] = $feature;
		}
		return $enabled;
	}

	/**
	 * Lists URL of enabled features
	 * @return array<string, string> Enabled features with URL
	 */
	public function listUrl(): array {
		$features = $this->read();
		$urls = [];
		foreach ($features as $feature => $configuration) {
			if ((!$configuration['enabled'] ?? true) || !isset($configuration['url'])) {
				continue;
			}
			$urls[$feature] = $configuration['url'];
		}
		return $urls;
	}

	/**
	 * Sets features enablement
	 * @param array<string> $names Feature names
	 * @param bool $enabled Are features enabled?
	 */
	public function setEnabled(array $names, bool $enabled = true): void {
		$config = $this->read();
		foreach ($names as $name) {
			if (!array_key_exists($name, $config)) {
				throw new FeatureNotFoundException($name);
			}
			$config[$name]['enabled'] = $enabled;
		}
		$this->write($config);
	}

}