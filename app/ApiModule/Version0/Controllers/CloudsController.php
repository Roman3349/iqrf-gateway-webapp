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
use Apitte\Core\Annotation\Controller\RequestBody;
use Apitte\Core\Annotation\Controller\Response;
use Apitte\Core\Annotation\Controller\Responses;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\CloudModule\Exceptions\CannotCreateCertificateDirectoryException;
use App\CloudModule\Exceptions\InvalidConnectionStringException;
use App\CloudModule\Models\AzureManager;
use App\CloudModule\Models\HexioManager;
use App\CloudModule\Models\IbmCloudManager;
use App\CloudModule\Models\InteliGlueManager;
use App\CoreModule\Exceptions\NonExistingJsonSchemaException;
use GuzzleHttp\Exception\TransferException;
use Nette\IOException;

/**
 * Cloud manager controller
 * @Path("/clouds")
 * @Tag("Clouds manager")
 */
class CloudsController extends BaseController {

	/**
	 * @var AzureManager Microsoft Azure IoT Hub connection manager
	 */
	private $azureManager;

	/**
	 * @var HexioManager Hexio IoT Platform connection manager
	 */
	private $hexioManager;

	/**
	 * @var IbmCloudManager IBM Cloud IoT connection manager
	 */
	private $ibmCloudManager;

	/**
	 * @var InteliGlueManager Inteliments InteliGlue connection manager
	 */
	private $inteliGlueManager;

	/**
	 * Constructor
	 * @param AzureManager $azureManager Microsoft Azure IoT Hub connection manager
	 * @param HexioManager $hexioManager Hexio IoT Platform connection manager
	 * @param IbmCloudManager $ibmManager IBM CLoud IoT connection manager
	 * @param InteliGlueManager $inteliGlueManager Inteliments InteliGlue connection manager
	 */
	public function __construct(AzureManager $azureManager, HexioManager $hexioManager, IbmCloudManager $ibmManager, InteliGlueManager $inteliGlueManager) {
		$this->azureManager = $azureManager;
		$this->hexioManager = $hexioManager;
		$this->ibmCloudManager = $ibmManager;
		$this->inteliGlueManager = $inteliGlueManager;
	}

	/**
	 * @Path("/azure")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Creates a new MQTT connection into Microsoft Azure IoT Hub
	 * ")
	 * @RequestBody(entity="\App\ApiModule\Version0\Entities\AzureEntity")
	 * @Responses({
	 *      @Response(code="201", description="Created"),
	 *      @Response(code="400", description="Bad response"),
	 *      @Response(code="500", description="Server error")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createAzure(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->azureManager->createMqttInterface($request->getJsonBody());
			return $response->withStatus(201, 'Created');
		} catch (InvalidConnectionStringException $e) {
			return $response->withStatus(400, 'Invalid connection string');
		} catch (NonExistingJsonSchemaException $e) {
			return $response->withStatus(400, 'Nonexisting JSON schema');
		} catch (IOException $e) {
			return $response->withStatus(500, 'Write failure');
		} catch (TransferException $e) {
			return $response->withStatus(500, 'Download failure');
		} catch (CannotCreateCertificateDirectoryException $e) {
			return $response->withStatus(500, 'Certificate directory creation failure');
		}
	}

	/**
	 * @Path("/hexio")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Creates a new MQTT connection into Hexio IoT Platform
	 * ")
	 * @RequestBody(entity="\App\ApiModule\Version0\Entities\HexioEntity")
	 * @Responses({
	 *      @Response(code="201", description="Created"),
	 *      @Response(code="400", description="Bad response"),
	 *      @Response(code="500", description="Server error")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createHexio(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->hexioManager->createMqttInterface($request->getJsonBody());
			return $response->withStatus(201, 'Created');
		} catch (NonExistingJsonSchemaException $e) {
			return $response->withStatus(400, 'Nonexisting JSON schema');
		} catch (IOException $e) {
			return $response->withStatus(500, 'Write failure');
		} catch (TransferException $e) {
			return $response->withStatus(500, 'Download failure');
		} catch (CannotCreateCertificateDirectoryException $e) {
			return $response->withStatus(500, 'Certificate directory creation failure');
		}
	}

	/**
	 * @Path("/ibmCloud")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Creates a new MQTT connection into IBM Cloud
	 * ")
	 * @RequestBody(entity="\App\ApiModule\Version0\Entities\IbmCloudEntity")
	 * @Responses({
	 *      @Response(code="201", description="Created"),
	 *      @Response(code="400", description="Bad response"),
	 *      @Response(code="500", description="Server error")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createIbmCloud(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->ibmCloudManager->createMqttInterface($request->getJsonBody());
			return $response->withStatus(201, 'Created');
		} catch (NonExistingJsonSchemaException $e) {
			return $response->withStatus(400, 'Nonexisting JSON schema');
		} catch (IOException $e) {
			return $response->withStatus(500, 'Write failure');
		} catch (TransferException $e) {
			return $response->withStatus(500, 'Download failure');
		} catch (CannotCreateCertificateDirectoryException $e) {
			return $response->withStatus(500, 'Certificate directory creation failure');
		}
	}

	/**
	 * @Path("/inteliGlue")
	 * @Method("POST")
	 * @OpenApi("
	 *   summary: Creates a new MQTT connection into Inteliments InteliGlue
	 * ")
	 * @RequestBody(entity="\App\ApiModule\Version0\Entities\InteliGlueEntity")
	 * @Responses({
	 *      @Response(code="201", description="Created"),
	 *      @Response(code="400", description="Bad response"),
	 *      @Response(code="500", description="Server error")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function createInteliGlue(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->inteliGlueManager->createMqttInterface($request->getJsonBody());
			return $response->withStatus(201, 'Created');
		} catch (NonExistingJsonSchemaException $e) {
			return $response->withStatus(400, 'Nonexisting JSON schema');
		} catch (IOException $e) {
			return $response->withStatus(500, 'Write failure');
		} catch (TransferException $e) {
			return $response->withStatus(500, 'Download failure');
		} catch (CannotCreateCertificateDirectoryException $e) {
			return $response->withStatus(500, 'Certificate directory creation failure');
		}
	}

}