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

namespace App\IqrfNetModule\Forms;

use App\CoreModule\Forms\FormFactory;
use App\CoreModule\Presenters\ProtectedPresenter;
use Nette\Forms\Form;
use Nette\SmartObject;

class ChangeAddressFormFactory {

	use SmartObject;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var ProtectedPresenter Protected presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(FormFactory $factory) {
		$this->factory = $factory;
	}

	/**
	 * Create change network device address form
	 * @param ProtectedPresenter $presenter Protected presenter
	 * @return Form Change network device address
	 */
	public function create(ProtectedPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('iqrfnet.changeAddress'));
		$form->addInteger('address', 'address')
			->addRule(Form::RANGE, 'messages.address', [0, 239])
			->setRequired('messages.address');
		$form->addSubmit('set', 'set');
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'onSuccess'];
		return $form;
	}

	/**
	 * Redirect on success
	 * @param Form $form Change device address form
	 */
	public function onSuccess(Form $form): void {
		$this->presenter->redirect('this', ['address' => $form->getValues()->address]);
	}

}
