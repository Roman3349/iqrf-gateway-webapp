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

use App\CoreModule\Traits\TPresenterFlashMessage;
use Nette\Security\IUserStorage;

/**
 * Protected presenter for protected application presenters
 */
abstract class ProtectedPresenter extends BasePresenter {

	use TPresenterFlashMessage;

	/**
	 * Checks requirements
	 * @param mixed $element Element
	 */
	public function checkRequirements($element): void {
		if (!$this->getUser()->isLoggedIn()) {
			if ($this->getUser()->getLogoutReason() === IUserStorage::INACTIVITY) {
				$this->flashInfo('core.signOut.inactivity');
			}
			$this->redirect(':Core:Sign:In', ['backlink' => $this->storeRequest()]);
		}
		parent::checkRequirements($element);
	}

}
