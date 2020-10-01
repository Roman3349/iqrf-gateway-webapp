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

namespace App\CoreModule\Presenters;

use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Localization\ITranslator;

/**
 * Base presenter for all application presenters
 */
abstract class BasePresenter extends Presenter {

	/**
	 * @var string Language
	 * @persistent
	 */
	public $lang;

	/**
	 * @var ITranslator Translator
	 */
	protected $translator;

	/**
	 * Injects the translator service
	 * @param ITranslator $translator Translator
	 */
	public function injectTranslator(ITranslator $translator): void {
		$this->translator = $translator;
	}

	/**
	 * Returns the translator
	 * @return ITranslator Translator
	 */
	public function getTranslator(): ITranslator {
		return $this->translator;
	}

	/**
	 * Creates an template
	 * @return ITemplate Template
	 */
	public function createTemplate(): ITemplate {
		$template = parent::createTemplate();
		assert($template instanceof Template);
		$template->setTranslator($this->translator);
		return $template;
	}

}
