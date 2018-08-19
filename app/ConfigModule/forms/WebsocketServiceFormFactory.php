<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
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

namespace App\ConfigModule\Forms;

use App\ConfigModule\Forms\GenericConfigFormFactory;
use App\ConfigModule\Model\GenericManager;
use App\ConfigModule\Presenters\WebsocketPresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Forms\Form;

/**
 * Websocket service configuration service form factory
 */
class WebsocketServiceFormFactory extends GenericConfigFormFactory {

	use Nette\SmartObject;

	/**
	 * @var int Websocket service ID
	 */
	private $id;

	/**
	 * @var array Files with websocket service instances
	 */
	private $instances;

	/**
	 * Constructor
	 * @param GenericManager $manager Generic configuration manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(GenericManager $manager, FormFactory $factory) {
		parent::__construct($manager, $factory);
		$this->manager->setComponent('shape::WebsocketCppService');
		$this->instances = $this->manager->getInstanceFiles();
		$this->redirect = 'Websocket:default';
	}

	/**
	 * Create websocket service configuration form
	 * @param WebsocketPresenter $presenter Websocket interface presenter
	 * @return Form Websocket service configuration form
	 */
	public function create(WebsocketPresenter $presenter): Form {
		$this->presenter = $presenter;
		$this->id = intval($presenter->getParameter('id'));
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.websocket.service.form'));
		$form->addText('instance', 'instance')->setRequired('messages.instance');
		$form->addInteger('WebsocketPort', 'WebsocketPort')->setRequired('messages.WebsocketPort');
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		if ($this->isExists()) {
			$this->manager->setFileName($this->instances[$this->id]);
			$form->setDefaults($this->manager->load());
		}
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Check if instance exists
	 * @return bool Is instance exists?
	 */
	public function isExists(): bool {
		return array_key_exists($this->id, $this->instances);
	}

}