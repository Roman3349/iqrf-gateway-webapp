<?php

/**
 * TEST: App\Models\Database\Entities\User
 * @covers App\Models\Database\Entities\User
 * @phpVersion >= 7.2
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Unit\Models\Database\Entities;

use App\Exceptions\InvalidUserLanguageException;
use App\Exceptions\InvalidUserRoleException;
use App\Models\Database\Entities\User;
use Nette\Security\Identity;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for user database entity
 */
final class UserTest extends TestCase {

	/**
	 * User name
	 */
	private const USERNAME = 'admin';

	/**
	 * Password
	 */
	private const PASSWORD = 'iqrf';

	/**
	 * Password hash
	 */
	private const HASH = '$2b$10$F91udIyWuySXoRsPlXDmSeNwM9vcuRWW/V0LXWtZ9tUfpC6YjUM8q';

	/**
	 * User role
	 */
	private const ROLE = User::ROLE_POWER;

	/**
	 * User language
	 */
	private const LANGUAGE = User::LANGUAGE_ENGLISH;

	/**
	 * @var User User entity
	 */
	private $entity;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->entity = new User(self::USERNAME, self::HASH, self::ROLE, self::LANGUAGE);
	}

	/**
	 * Tests the function to get the user's ID
	 */
	public function testGetId(): void {
		Assert::null($this->entity->getId());
	}

	/**
	 * Tests the function to get the user name
	 */
	public function testGetUserName(): void {
		Assert::same(self::USERNAME, $this->entity->getUserName());
	}

	/**
	 * Tests the function to get the user's password hash
	 */
	public function testGetPassword(): void {
		Assert::same(self::HASH, $this->entity->getPassword());
	}

	/**
	 * Tests the function to get the user's role
	 */
	public function testGetRole(): void {
		Assert::same(self::ROLE, $this->entity->getRole());
	}

	/**
	 * Tests the function to get the user's language
	 */
	public function testGetLanguage(): void {
		Assert::same(self::LANGUAGE, $this->entity->getLanguage());
	}

	/**
	 * Tests the function to set the username
	 */
	public function testSetUserName(): void {
		$username = 'iqrf';
		$this->entity->setUserName($username);
		Assert::same($username, $this->entity->getUserName());
	}

	/**
	 * Tests the function to set the user's password
	 */
	public function testSetPassword(): void {
		$password = 'admin';
		$this->entity->setPassword($password);
		Assert::true($this->entity->verifyPassword($password));
	}

	/**
	 * Tests the function to set the user's role
	 */
	public function testSetRole(): void {
		$role = User::ROLE_NORMAL;
		$this->entity->setRole($role);
		Assert::same($role, $this->entity->getRole());
	}

	/**
	 * Tests the function to set the user's role
	 */
	public function testSetRoleInvalid(): void {
		Assert::exception(function (): void {
			$this->entity->setRole('invalid');
		}, InvalidUserRoleException::class);
		Assert::same(User::ROLE_POWER, $this->entity->getRole());
	}

	/**
	 * Tests the function to set the user's language
	 */
	public function testSetLanguage(): void {
		$language = User::LANGUAGE_ENGLISH;
		$this->entity->setLanguage($language);
		Assert::same($language, $this->entity->getLanguage());
	}

	/**
	 * Tests the function to set the user's language
	 */
	public function testSetLanguageInvalid(): void {
		Assert::exception(function (): void {
			$this->entity->setLanguage('invalid');
		}, InvalidUserLanguageException::class);
		Assert::same(User::LANGUAGE_DEFAULT, $this->entity->getLanguage());
	}

	/**
	 * Tests the function to verify the user's password
	 */
	public function testVerifyPassword(): void {
		Assert::true($this->entity->verifyPassword(self::PASSWORD));
		Assert::false($this->entity->verifyPassword('admin'));
	}

	/**
	 * Tests the function to convert the entity into an array
	 */
	public function testToArray(): void {
		$expected = [
			'id' => null,
			'username' => self::USERNAME,
			'password' => self::HASH,
			'role' => self::ROLE,
			'language' => self::LANGUAGE,
		];
		Assert::same($expected, $this->entity->toArray());
	}

	/**
	 * Tests the function to convert the entity into Nette Identity
	 */
	public function testToIdentity(): void {
		$data = [
			'username' => self::USERNAME,
			'language' => self::LANGUAGE,
		];
		$expected = new Identity(null, [self::ROLE], $data);
		$actual = $this->entity->toIdentity();
		Assert::equal($expected, $actual);
		Assert::same([self::ROLE], $actual->getRoles());
		Assert::null($actual->getId());
	}

}

$test = new UserTest();
$test->run();
