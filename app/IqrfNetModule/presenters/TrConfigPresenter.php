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

namespace App\IqrfNetModule\Presenters;

use App\CoreModule\Presenters\ProtectedPresenter;
use App\IqrfNetModule\Forms\ChangeAddressFormFactory;
use Nette\Forms\Form;

/**
 * IQMESH Network Manager - TR security presenter
 */
class TrConfigPresenter extends ProtectedPresenter {

	/**
	 * @var ChangeAddressFormFactory Change device address form
	 * @inject
	 */
	public $changeAddressForm;

	/**
	 * Create change device address form
	 * @return Form Change device address form
	 */
	protected function createComponentIqrfNetAddressForm(): Form {
		return $this->changeAddressForm->create($this);
	}

}