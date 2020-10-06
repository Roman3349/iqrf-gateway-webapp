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

namespace App\ApiModule\Version0\Controllers;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\OpenApi;
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\Models\Database\EntityManager;
use Nettrine\Migrations\ContainerAwareConfiguration as DoctrineConfiguration;

/**
 * Installation controller
 * @Path("/installation")
 * @Tag("Installation manager")
 */
class InstallationController extends BaseController {

	/**
	 * @var DoctrineConfiguration Doctrine configuration
	 */
	private $doctrineConfiguration;

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * Constructor
	 * @param DoctrineConfiguration $doctrineConfiguration Doctrine configuration
	 * @param EntityManager $entityManager Database entity manager
	 */
	public function __construct(DoctrineConfiguration $doctrineConfiguration, EntityManager $entityManager) {
		$this->doctrineConfiguration = $doctrineConfiguration;
		$this->entityManager = $entityManager;
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Checks the installation
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/InstallationCheck'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function check(ApiRequest $request, ApiResponse $response): ApiResponse {
		$availableMigrations = $this->doctrineConfiguration->getNumberOfAvailableMigrations();
		$executedMigrations = $this->doctrineConfiguration->getNumberOfExecutedMigrations();
		$users = $this->entityManager->getUserRepository()->count([]);
		return $response->writeJsonBody([
			'allMigrationsExecuted' => $availableMigrations === $executedMigrations,
			'hasUsers' => $users !== 0,
		]);
	}

}