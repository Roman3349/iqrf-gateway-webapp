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

namespace App\IqrfNetModule\Presenters;

use App\CoreModule\Presenters\ProtectedPresenter;
use App\CoreModule\Traits\TPresenterFlashMessage;
use App\IqrfNetModule\Forms\TrUploadFormFactory;
use Nette\Application\UI\Form;

/**
 * IQRF TR native upload presenter
 */
class TrUploadPresenter extends ProtectedPresenter {

	use TPresenterFlashMessage;

	/**
	 * @var TrUploadFormFactory IQRF TR native upload form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * Checks if the uploader is enabled
	 */
	protected function startup(): void {
		parent::startup();
		if (!$this->context->parameters['features']['trUpload']) {
			$this->flashError('iqrfnet.trUpload.messages.disabled');
			$this->redirect('Homepage:default');
		}
	}

	/**
	 * Creates IQRF TR native upload form
	 * @return Form IQRF TR native upload form
	 */
	protected function createComponentTrUploadForm(): Form {
		return $this->formFactory->create($this);
	}

}