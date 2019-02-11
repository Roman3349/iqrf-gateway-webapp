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

namespace Tests\Toolkit\TestCases;

use App\CoreModule\Models\CommandManager;
use Mockery;
use Mockery\MockInterface;
use Tester\TestCase;

/**
 * Shell command test case
 */
abstract class CommandTestCase extends TestCase {

	/**
	 * @var CommandManager|MockInterface Mocked command manager
	 */
	protected $commandManager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->commandManager = Mockery::mock(CommandManager::class);
	}

	/**
	 * Cleanups the test environment
	 */
	protected function tearDown(): void {
		Mockery::close();
	}

	/**
	 * Receives a command
	 * @param string $command Command
	 * @param bool $needSudo Is sudo needed?
	 * @param string $output Command's output
	 */
	protected function receiveCommand(string $command, ?bool $needSudo = null, ?string $output = null): void {
		$process = $this->commandManager->shouldReceive('run');
		if ($needSudo === null) {
			$process->with($command);
		} else {
			$process->with($command, $needSudo);
		}
		if ($output !== null) {
			$process->andReturn($output);
		}
	}

	/**
	 * Receives a command existence check
	 * @param string $command Command
	 * @param bool $output Is the command exist?
	 */
	protected function receiveCommandExist(string $command, bool $output): void {
		$this->commandManager->shouldReceive('commandExist')
			->with($command)->andReturn($output);
	}

	/**
	 * Receives an async command
	 * @param callable $callback Command's callback
	 * @param string $command Command
	 * @param bool|null $needSudo Is sudo needed?
	 */
	protected function receiveAsyncCommand(callable $callback, string $command, ?bool $needSudo = null): void {
		$process = $this->commandManager->shouldReceive('runAsync');
		if ($needSudo === null) {
			$process->with($callback, $command);
		} else {
			$process->with($callback, $command, $needSudo);
		}
	}

}
