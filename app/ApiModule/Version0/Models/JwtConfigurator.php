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

namespace App\ApiModule\Version0\Models;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;

/**
 * JWT configurator
 */
class JwtConfigurator {

	/**
	 * Private key path
	 */
	private const KEY_PATH = '/etc/iqrf-gateway-webapp/certs/privkey.pem';

	/**
	 * Creates a JWT configuration
	 * @return Configuration JWT configuration
	 */
	public function create(): Configuration {
		if (is_readable(self::KEY_PATH)) {
			return Configuration::forSymmetricSigner(new Sha256(), new Key('file://' . self::KEY_PATH));
		}
		return Configuration::forUnsecuredSigner();
	}

}