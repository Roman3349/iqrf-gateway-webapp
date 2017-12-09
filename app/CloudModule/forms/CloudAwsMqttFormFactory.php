<?php

/**
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

namespace App\CloudModule\Forms;

use App\CloudModule\Model\AwsManager;
use App\CloudModule\Presenters\AwsPresenter;
use App\ConfigModule\Model\BaseServiceManager;
use App\ConfigModule\Model\InstanceManager;
use App\Forms\FormFactory;
use Nette;
use Nette\Application\UI\Form;
use Nette\IOException;

class CloudAwsMqttFormFactory {

	use Nette\SmartObject;

	/**
	 * @var AwsManager
	 */
	private $cloudManager;

	/**
	 * @var BaseServiceManager
	 */
	private $baseService;

	/**
	 * @var InstanceManager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param AwsManager $aws
	 * @param BaseServiceManager $baseService
	 * @param InstanceManager $manager
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(AwsManager $aws, BaseServiceManager $baseService, InstanceManager $manager, FormFactory $factory) {
		$this->cloudManager = $aws;
		$this->baseService = $baseService;
		$this->manager = $manager;
		$this->factory = $factory;
	}

	/**
	 * Create MQTT configuration form
	 * @param AwsPresenter $presenter
	 * @return Form MQTT configuration form
	 */
	public function create(AwsPresenter $presenter) {
		$form = $this->factory->create();
		$fileName = 'MqttMessaging';
		$this->manager->setFileName($fileName);
		$form->addText('endpoint', 'Endpoint')->setRequired();
		$form->addUpload('caCert', 'Root CA certificate')->setRequired();
		$form->addUpload('cert', 'Certificate')->setRequired();
		$form->addUpload('key', 'Private key')->setRequired();
		$form->addSubmit('save', 'Save');
		$form->addProtection('Timeout expired, resubmit the form.');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			try {
				$settings = $this->cloudManager->createMqttInterface($values);
				$baseService = $this->cloudManager->createBaseService();
				$this->baseService->add($baseService);
				$this->manager->add($settings);
				$presenter->redirect(':Config:Mqtt:default');
			} catch (IOException $e) {
				$presenter->flashMessage('IQRF Daemon\'s configuration files not found.', 'danger');
			}
		};
		return $form;
	}

}