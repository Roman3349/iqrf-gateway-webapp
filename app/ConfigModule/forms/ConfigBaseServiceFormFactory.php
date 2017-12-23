<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
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
declare(strict_types=1);

namespace App\ConfigModule\Forms;

use App\ConfigModule\Model\BaseServiceManager;
use App\ConfigModule\Model\InstanceManager;
use App\ConfigModule\Presenters\BaseServicePresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Application\UI\Form;

class ConfigBaseServiceFormFactory {

	use Nette\SmartObject;

	/**
	 * @var InstanceManager Instances manager
	 */
	private $instanceManager;

	/**
	 * @var BaseServiceManager Base service manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param BaseServiceManager $manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(BaseServiceManager $manager, FormFactory $factory, InstanceManager $instanceManager) {
		$this->manager = $manager;
		$this->factory = $factory;
		$this->instanceManager = $instanceManager;
	}

	/**
	 * Create MQTT configuration form
	 * @param BaseServicePresenter $presenter
	 * @return Form MQTT configuration form
	 */
	public function create(BaseServicePresenter $presenter): Form {
		$id = intval($presenter->getParameter('id'));
		$form = $this->factory->create();
		$serializers = [
			'JsonSerializer' => 'JsonSerializer',
			'SimpleSerializer' => 'SimpleSerializer',
		];
		$this->instanceManager->setFileName('MqMessaging');
		$mqInstances = $this->instanceManager->getInstancesNames();
		$this->instanceManager->setFileName('MqttMessaging');
		$mqttInstances = $this->instanceManager->getInstancesNames();
		$instances = array_merge($mqInstances, $mqttInstances);
		$form->addText('Name', 'Name')->setRequired();
		$form->addSelect('Messaging', 'Messaging', $instances)->setRequired()->setTranslator();
		$form->addCheckboxList('Serializers', 'Serializers', $serializers)->setRequired();
		$prop = $form->addContainer('Properties');
		$prop->addCheckbox('AsyncDpaMessage', 'AsyncDpaMessage');
		$form->addSubmit('save', 'Save');
		$form->setDefaults($this->manager->load($id));
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter, $id) {
			$this->manager->save($values, $id);
			$presenter->redirect('BaseService:default');
		};
		return $form;
	}

}
