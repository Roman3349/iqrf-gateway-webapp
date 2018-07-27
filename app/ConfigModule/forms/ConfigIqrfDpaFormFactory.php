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

use App\ConfigModule\Model\GenericManager;
use App\ConfigModule\Presenters\IqrfDpaPresenter;
use App\Forms\FormFactory;
use Nette;
use Nette\Forms\Form;
use Nette\IOException;

class ConfigIqrfDpaFormFactory {

	use Nette\SmartObject;

	/**
	 * @var GenericManager Generic config manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param GenericManager $manager Generic config manager
	 */
	public function __construct(FormFactory $factory, GenericManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Create IQRF configuration form
	 * @param IqrfDpaPresenter $presenter
	 * @return Form IQRF DPA interface configuration form
	 */
	public function create(IqrfDpaPresenter $presenter): Form {
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('config.iqrfDpa.form'));
		$communicationModes = ['STD' => 'CommunicationModes.STD', 'LP' => 'CommunicationModes.LP'];
		$responseTimes = ['k40Ms', 'k360Ms', 'k680Ms', 'k1320Ms', 'k2600Ms',
			'k5160Ms', 'k10280Ms', 'k20620Ms',];
		foreach ($responseTimes as $key => $time) {
			unset($responseTimes[$key]);
			$responseTimes[$time] = 'ResponseTimes.' . $time;
		}
		$this->manager->setComponent('iqrf::IqrfDpa');
		$this->manager->setFileName($this->manager->getInstanceFiles()[0]);
		$form->addText('instance', 'instance')->setRequired('messages.instance');
		$form->addInteger('DpaHandlerTimeout', 'DpaHandlerTimeout')->setRequired('messages.DpaHandlerTimeout')
				->addRule(Form::MIN, 'messages.DpaHandlerTimeout-rule', 0);
		$form->addSelect('CommunicationMode', 'CommunicationMode', $communicationModes);
		$form->addInteger('BondedNodes', 'BondedNodes');
		$form->addInteger('DiscoveredNodes', 'DiscoveredNodes');
		$form->addSelect('ResponseTime', 'ResponseTime', $responseTimes);
		$form->addSubmit('save', 'Save');
		$form->setDefaults($this->manager->load());
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = function (Form $form, $values) use ($presenter) {
			try {
				$this->manager->save($values);
				$presenter->flashMessage('config.messages.success', 'success');
			} catch (IOException $e) {
				$presenter->flashMessage('config.messages.writeFailure', 'danger');
			} finally {
				$presenter->redirect('Homepage:default');
			}
		};
		return $form;
	}

}
