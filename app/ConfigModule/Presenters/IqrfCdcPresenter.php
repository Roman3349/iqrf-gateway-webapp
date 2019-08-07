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

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Forms\IqrfCdcFormFactory;
use App\ConfigModule\Models\GenericManager;
use App\ConfigModule\Models\IqrfManager;
use Nette\Application\UI\Form;
use Nette\Utils\JsonException;

/**
 * IQRF CDC configuration presenter
 */
class IqrfCdcPresenter extends GenericPresenter {

	/**
	 * @var IqrfCdcFormFactory IQRF CDC interface configuration form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var IqrfManager IQRF interface manager
	 */
	private $iqrfManager;

	/**
	 * Constructor
	 * @param IqrfManager $iqrfManager IQRF interface manager
	 * @param GenericManager $genericManager Generic configuration manager
	 */
	public function __construct(IqrfManager $iqrfManager, GenericManager $genericManager) {
		$this->iqrfManager = $iqrfManager;
		$components = ['iqrf::IqrfCdc'];
		parent::__construct($components, $genericManager);
	}

	/**
	 * Renders the IQRF CDC interface configurator
	 */
	public function renderDefault(): void {
		$this->template->interfaces = $this->iqrfManager->getCdcInterfaces();
	}

	/**
	 * Creates the IQRF CDC interface configuration form
	 * @return Form IQRF CDC interface configuration form
	 * @throws JsonException
	 */
	protected function createComponentConfigIqrfCdcForm(): Form {
		return $this->formFactory->create($this);
	}

}