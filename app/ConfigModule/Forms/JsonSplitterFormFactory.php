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

namespace App\ConfigModule\Forms;

use App\ConfigModule\Presenters\JsonSplitterPresenter;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * JSON Splitter form factory
 */
class JsonSplitterFormFactory extends GenericConfigFormFactory {

	use SmartObject;

	/**
	 * Creates the JSON splitter service configuration form
	 * @param JsonSplitterPresenter $presenter JSON Splitter configuration presenter
	 * @return Form JSON splitter configuration form
	 * @throws JsonException
	 */
	public function create(JsonSplitterPresenter $presenter): Form {
		$this->manager->setComponent('iqrf::JsonSplitter');
		$this->presenter = $presenter;
		$form = $this->factory->create('config.jsonSplitter.form');
		$form->addText('instance', 'instance')
			->setRequired('messages.instance');
		$form->addCheckbox('validateJsonResponse', 'validateJsonResponse');
		$form->addSubmit('save', 'Save');
		$form->addProtection('core.errors.form-timeout');
		$form->setDefaults($this->manager->load(0));
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

}
