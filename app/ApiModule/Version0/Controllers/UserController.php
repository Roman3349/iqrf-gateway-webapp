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
use Apitte\Core\Annotation\Controller\RequestParameter;
use Apitte\Core\Annotation\Controller\RequestParameters;
use Apitte\Core\Annotation\Controller\Response;
use Apitte\Core\Annotation\Controller\Responses;
use Apitte\Core\Annotation\Controller\Tag;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;
use Nette\Security\User;

/**
 * User manager API controller
 * @Path("/user")
 * @Tag("User manager")
 */
class UserController extends BaseController {

	/**
	 * @var User User
	 */
	private $user;

	public function __construct(User $user) {
		$this->user = $user;
	}

	/**
	 * Get an information about logged in user
	 * @Path("/")
	 * @Method("GET")
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="401", description="Unauthorized")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function get(ApiRequest $request, ApiResponse $response): ApiResponse {
		if (!$this->user->isLoggedIn()) {
			return $response->withStatus(401);
		}
		/**
		 * @var Identity Identity
		 */
		$identity = $this->user->getIdentity();
		$data = $identity->getData();
		return $response->writeJsonBody([
				'id' => $this->user->getId(),
				'username' => $data['username'],
				'language' => $data['language'],
				'roles' => $this->user->getRoles(),
			]);
	}

	/**
	 * @Path("/signIn")
	 * @Method("POST")
	 * @Responses({
	 *      @Response(code="200", description="Success"),
	 *      @Response(code="400", description="Bad request")
	 * })
	 * @RequestParameters({
	 *      @RequestParameter(name="username", type="string", in="query", description="Username"),
	 *      @RequestParameter(name="password", type="string", in="query", description="Password")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function signIn(ApiRequest $request, ApiResponse $response): ApiResponse {
		try {
			$this->user->login($request->getParameter('username'), $request->getParameter('password'));
		} catch (AuthenticationException $e) {
			return $response->withStatus(400);
		}
		return $response;
	}

	/**
	 * @Path("/signOut")
	 * @Method("GET")
	 * @Responses({
	 *      @Response(code="200", description="Success")
	 * })
	 * @param ApiRequest $request API request
	 * @param ApiResponse $response API response
	 * @return ApiResponse API response
	 */
	public function signOut(ApiRequest $request, ApiResponse $response): ApiResponse {
		$this->user->logout();
		return $response;
	}

}