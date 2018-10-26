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

namespace App\IqrfNetModule\Forms;

use App\CoreModule\Forms\FormFactory;
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\InvalidRfChannelTypeException;
use App\IqrfNetModule\Models\IqrfNetManager;
use App\IqrfNetModule\Presenters\NetworkPresenter;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * IQMESH RF form factory
 */
class RfFormFactory {

	use SmartObject;

	/**
	 * @var IqrfNetManager IQMESH Network manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var string RF Band
	 */
	private $rfBand;

	/**
	 * @var NetworkPresenter IQMESH Network presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param IqrfNetManager $manager IQMESH Network manager
	 */
	public function __construct(FormFactory $factory, IqrfNetManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Create IQMESH Access password form
	 * @param NetworkPresenter $presenter IQMESH Network presenter
	 * @return Form IQMESH Access password form
	 * @throws JsonException
	 */
	public function create(NetworkPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create();
		$form->setTranslator($form->getTranslator()->domain('iqrfnet.network-manager.rf-settings'));
		$rfBands = [
			'443 MHz' => 'rfBands.443',
			'868 MHz' => 'rfBands.868',
			'916 MHz' => 'rfBands.916',
			'ERROR' => 'rfBands.error',
		];
		$types = [
			IqrfNetManager::MAIN_RF_CHANNEL_A => 'rfChannelTypes.main-a',
			IqrfNetManager::MAIN_RF_CHANNEL_B => 'rfChannelTypes.main-b',
			IqrfNetManager::ALTERNATIVE_RF_CHANNEL_A => 'rfChannelTypes.alternative-a',
			IqrfNetManager::ALTERNATIVE_RF_CHANNEL_B => 'rfChannelTypes.alternative-b',
		];
		try {
			$this->rfBand = $this->manager->readHwpConfiguration()['rfBand'] ?? 'ERROR';
		} catch (DpaErrorException | EmptyResponseException $e) {
			$this->rfBand = 'ERROR';
		}
		$form->addSelect('rfBand', 'rfBand', $rfBands)->setDisabled()
			->setDefaultValue($this->rfBand);
		$form->addInteger('rfChannel', 'rfChannel')
			->setRequired('messages.rfChannel')
			->addConditionOn($form['rfBand'], Form::EQUAL, '443 MHz')
			->addRule(Form::RANGE, 'messages.rfChannel443', [0, 16])
			->elseCondition($form['rfBand'], Form::EQUAL, '868 MHz')
			->addRule(Form::RANGE, 'messages.rfChannel868', [0, 67])
			->elseCondition($form['rfBand'], Form::EQUAL, '916 MHz')
			->addRule(Form::RANGE, 'messages.rfChannel916', [0, 255]);
		$form->addSelect('type', 'rfChannelType', $types);
		$form->addSubmit('set', 'set')->onClick[] = [$this, 'setChannel'];
		$form->addProtection('core.errors.form-timeout');
		return $form;
	}

	/**
	 * Set RF channel
	 * @param SubmitButton $button Submit button for setting RF channel
	 * @throws InvalidRfChannelTypeException
	 * @throws JsonException
	 */
	public function setChannel(SubmitButton $button): void {
		$form = $button->getForm();
		$values = $form->getValues();
		if ($this->rfBand === 'ERROR') {
			$form->addError('Invalid RF Band.');
		} else {
			try {
				$this->manager->setRfChannel($values['rfChannel'], strval($values['type']));
			} catch (EmptyResponseException | DpaErrorException $e) {
				$message = 'No response from IQRF Gateway Daemon.';
				$form->addError($message);
				$this->presenter->flashMessage($message, 'danger');
			}
		}
	}

}