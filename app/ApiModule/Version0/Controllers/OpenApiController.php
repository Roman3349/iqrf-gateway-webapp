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
use Apitte\Core\Annotation\Controller\Path;
use Apitte\Core\Annotation\Controller\Response;
use Apitte\Core\Annotation\Controller\Responses;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Apitte\OpenApi\ISchemaBuilder;

/**
 * OpenAPI controller
 * @Path("/openapi")
 * @Tag("OpenAPI")
 */
class OpenApiController extends BaseController {

	/**
	 * @var ISchemaBuilder OpenAPI schema builder
	 */
	private $schemaBuilder;

	/**
	 * Constructor
	 * @param ISchemaBuilder $schemaBuilder OpenAPI schema builder
	 */
	public function __construct(ISchemaBuilder $schemaBuilder) {
		$this->schemaBuilder = $schemaBuilder;
	}

	/**
	 * Returns OpenAPI schema
	 * @Path("/")
	 * @Method("GET")
	 * @Responses({
	 *      @Response(code="200", description="Success")
	 * })
	 */
	public function index(ApiRequest $request, ApiResponse $response): ApiResponse {
		$openApi = $this->schemaBuilder->build();
		return $response->writeJsonBody($openApi->toArray());
	}

}