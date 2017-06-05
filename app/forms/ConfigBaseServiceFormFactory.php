<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
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

namespace App\Forms;

use App\Model\ConfigManager;
use App\Model\ConfigParser;
use App\Presenters\ConfigPresenter;
use Nette;
use Nette\Application\UI\Form;

class ConfigBaseServiceFormFactory {

	use Nette\SmartObject;

	/**
	 * @var ConfigManager
	 * @inject
	 */
	private $configManager;

	/**
	 * @var ConfigParser
	 * @inject
	 */
	private $configParser;

	/**
	 * @var FormFactory
	 * @inject
	 */
	private $factory;

	public function __construct(FormFactory $factory, ConfigManager $configManager, ConfigParser $configParser) {
		$this->factory = $factory;
		$this->configManager = $configManager;
		$this->configParser = $configParser;
	}

	/**
	 * Create MQTT configuration form
	 * @param ConfigPresenter $presenter
	 * @return Form MQTT configuration form
	 */
	public function create(ConfigPresenter $presenter) {
		$id = $presenter->id;
		$form = $this->factory->create();
		$fileName = 'BaseService';
		$json = $this->configManager->read($fileName);
		$serializers = ['SimpleSerializer' => 'SimpleSerializer', 'JsonSerializer' => 'JsonSerializer'];
		$form->addText('Name', 'Name');
		$form->addText('Messaging', 'Messaging');
		$form->addCheckboxList('Serializers', 'Serializers', $serializers);
		$form->addSubmit('save', 'Save');
		$form->setDefaults($this->configParser->baseServiceToForm($json, $id));
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter, $id) {
			$this->configManager->saveBaseService($values, $id);
			$presenter->redirect('Config:default');
		};

		return $form;
	}

}
