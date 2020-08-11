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
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\RequestParameters;
use Apitte\Core\Annotation\Controller\Response;
use Apitte\Core\Annotation\Controller\Responses;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\Models\Database\Entities\ApiKey;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\ApiKeyRepository;
use DateTime;
use Nette\Utils\JsonException;
use Throwable;
use function assert;

/**
 * API keys controller
 * @Path("/apiKeys")
 * @Tag("API key manager")
 */
class ApiKeyController extends BaseController {

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * @var ApiKeyRepository API key database repository
	 */
	private $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(EntityManager $entityManager) {
		$this->entityManager = $entityManager;
		$this->repository = $entityManager->getApiKeyRepository();
	}

	/**
	 * @Path("/")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Lists all API keys
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      type: array
	 *                      items:
	 *                          $ref: '#/components/schemas/ApiKeyDetail'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function list(ApiRequest $request, ApiResponse $response): ApiResponse {
		$apiKeys = $this->repository->findAll();
		return $response->writeJsonBody($apiKeys);
	}

	/**
	 * @Path("/")
	 * @Method("POST")
	 * @OpenApi("
	 *  summary: Creates a new API key
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/ApiKeyModify'
	 *  responses:
	 *      '201':
	 *          description: Created
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/ApiKeyCreated'
	 *      '400':
	 *          description: 'Bad request'
	 * ")
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function create(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$json = $request->getJsonBody(false);
		} catch (JsonException $e) {
			return $response->withStatus(ApiResponse::S400_BAD_REQUEST, 'Invalid JSON syntax');
		}
		if ($json->expiration === null) {
			$expiration = null;
		} else {
			try {
				$expiration = new DateTime($json->expiration);
			} catch (Throwable $e) {
				return $response->withStatus(ApiResponse::S400_BAD_REQUEST, 'Invalid expiration date');
			}
		}
		$apiKey = new ApiKey($json->description, $expiration);
		$this->entityManager->persist($apiKey);
		$this->entityManager->flush();
		return $response->writeJsonObject($apiKey)
			->withStatus(ApiResponse::S201_CREATED);
	}

	/**
	 * @Path("/{id}")
	 * @Method("GET")
	 * @OpenApi("
	 *  summary: Finds API key by ID
	 *  responses:
	 *      '200':
	 *          description: Success
	 *          content:
	 *              application/json:
	 *                  schema:
	 *                      $ref: '#/components/schemas/ApiKeyDetail'
	 *      '404':
	 *          description: Not found
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="API key ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		$apiKey = $this->repository->find($id);
		if ($apiKey === null) {
			return $response->withStatus(ApiResponse::S404_NOT_FOUND, 'API key not found');
		}
		assert($apiKey instanceof ApiKey);
		return $response->writeJsonObject($apiKey);
	}

	/**
	 * @Path("/{id}")
	 * @Method("DELETE")
	 * @OpenApi("
	 *   summary: Deletes a API key
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="API key ID")
	 * })
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="404", description="Not found")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function delete(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		$apiKey = $this->repository->find($id);
		if ($apiKey === null) {
			return $response->withStatus(ApiResponse::S404_NOT_FOUND, 'API key not found');
		}
		$this->entityManager->remove($apiKey);
		$this->entityManager->flush();
		return $response;
	}

	/**
	 * @Path("/{id}")
	 * @Method("PUT")
	 * @OpenApi("
	 *  summary: Edits the API key
	 *  requestBody:
	 *      required: true
	 *      content:
	 *          application/json:
	 *              schema:
	 *                  $ref: '#/components/schemas/ApiKeyModify'
	 *  responses:
	 *      '200':
	 *          description: Success
	 *      '400':
	 *          description: 'Bad request'
	 *      '404':
	 *          description: 'API key not found'
	 * ")
	 * @RequestParameters({
	 *      @RequestParameter(name="id", type="integer", description="API key ID")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function edit(ApiRequest $request, ApiResponse $response): ApiResponse {
		$id = (int) $request->getParameter('id');
		$apiKey = $this->repository->find($id);
		if ($apiKey === null) {
			return $response->withStatus(ApiResponse::S404_NOT_FOUND, 'API key not found');
		}
		assert($apiKey instanceof ApiKey);
		try {
			$json = $request->getJsonBody(false);
		} catch (JsonException $e) {
			return $response->withStatus(ApiResponse::S400_BAD_REQUEST, 'Invalid JSON syntax');
		}
		$apiKey->setDescription($json->description);
		if ($json->expiration === null) {
			$expiration = null;
		} else {
			try {
				$expiration = new DateTime($json->expiration);
			} catch (Throwable $e) {
				return $response->withStatus(ApiResponse::S400_BAD_REQUEST, 'Invalid expiration date');
			}
		}
		$apiKey->setExpiration($expiration);
		$this->entityManager->persist($apiKey);
		$this->entityManager->flush();
		return $response;
	}

}
