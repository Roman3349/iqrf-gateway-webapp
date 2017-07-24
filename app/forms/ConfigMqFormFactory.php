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

use App\Forms\FormFactory;
use App\Model\ConfigManager;
use App\Model\ConfigParser;
use App\Presenters\ConfigPresenter;
use Nette;
use Nette\Application\UI\Form;

class ConfigMqFormFactory {

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

	/**
	 * Constructor
	 * @param FormFactory $factory
	 * @param ConfigManager $configManager
	 * @param ConfigParser $configParser
	 */
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
		$form = $this->factory->create();
		$fileName = 'MqMessaging';
		$json = $this->configManager->read($fileName);
		$form->addText('Name', 'Name')->setRequired();
		$form->addCheckbox('Enabled', 'Enabled');
		$form->addText('LocalMqName', 'LocalMqName')->setRequired();
		$form->addText('RemoteMqName', 'RemoteMqName')->setRequired();
		$form->addSubmit('save', 'Save');
		$form->setDefaults($this->configParser->instancesToForm($json));
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter, $fileName) {
			$this->configManager->saveInstances($fileName, $values);
			$presenter->redirect('Config:default');
		};
		return $form;
	}

}
