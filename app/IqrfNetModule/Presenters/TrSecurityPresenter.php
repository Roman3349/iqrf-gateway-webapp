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

use App\IqrfNetModule\Forms\SecurityFormFactory;
use Nette\Application\UI\Form;

/**
 * IQMESH security presenter
 */
class TrSecurityPresenter extends TrConfigPresenter {

	/**
	 * @var SecurityFormFactory IQMESH Security configuration form
	 * @inject
	 */
	public $form;

	/**
	 * Creates the IQMESH Security configuration form
	 * @return Form IQMESH Security configuration form
	 */
	protected function createComponentIqrfNetSecurityForm(): Form {
		return $this->form->create($this);
	}

}
