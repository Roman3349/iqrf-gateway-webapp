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

namespace App\Presenters;

use App\Forms\UserAddFormFactory;
use App\Forms\UserEditFormFactory;
use App\Model\UserManager;
use Nette\Forms\Form;
use Nette\Database\Context;
use Nette\Database\Table\Selection;

/**
 * User presenter
 */
class UserPresenter extends ProtectedPresenter {

	/**
	 * @var UserAddFormFactory Add a new user form factory
	 * @inject
	 */
	public $addFormFactory;

	/**
	 * @var UserEditFormFactory Edit an existing user form factory
	 * @inject
	 */
	public $editFormFactory;

	/**
	 * @var Selection Database table selection
	 */
	private $table;

	/**
	 * @var UserManager User manager
	 */
	private $userManager;

	/**
	 * Constructor
	 * @param Context $database Database contaxt
	 * @param UserManager $userManager User manager
	 */
	public function __construct(Context $database, UserManager $userManager) {
		$this->table = $database->table('users');
		$this->userManager = $userManager;
		parent::__construct();
	}

	/**
	 * Render a list of users
	 */
	public function renderDefault() {
		$this->template->users = $this->table;
	}

	/**
	 * Render form for editing users
	 * @param int $id User ID
	 */
	public function renderEdit(int $id) {
		$this->template->id = $id;
	}

	/**
	 * Delete an user
	 * @param int $id User ID
	 */
	public function actionDelete(int $id) {
		$user = $this->userManager->getInfo($id);
		$this->userManager->delete($id);
		$message = $this->translator->translate('core.user.form.messages.successDelete', ['username' => $user['username']]);
		$this->flashMessage($message, 'success');
		$this->redirect('User:default');
		$this->setView('default');
	}

	/**
	 * Create add a new user form
	 * @return Form Add a new user form
	 */
	protected function createComponentUserAddForm() {
		return $this->addFormFactory->create($this);
	}

	/**
	 * Create edit and existing user form
	 * @return Form Edit an existing user form
	 */
	protected function createComponentUserEditForm() {
		return $this->editFormFactory->create($this);
	}

}