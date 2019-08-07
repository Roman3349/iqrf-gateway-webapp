<?php

/**
 * TEST: App\CoreModule\Router\RouterFactory
 * @covers App\CoreModule\Router\RouterFactory
 * @phpVersion >= 7.1
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Integration\CoreModule\Router;

use App\CoreModule\Router\RouterFactory;
use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for the router factory
 */
class RouterFactoryTest extends TestCase {

	/**
	 * @var mixed[] Routes
	 */
	private $expected = [
		['Cloud:' => [
			'[<lang [a-z]{2}>/]cloud/<presenter>/<action>[/<id>]',
		],],
		['Config:' => [
			'[<lang [a-z]{2}>/]config/scheduler/add/<type>',
			'[<lang [a-z]{2}>/]config/<presenter>/<action>[/<id>]',
		],],
		['Gateway:' => [
			'[<lang [a-z]{2}>/]gateway/<presenter>/<action>',
		],],
		['Install:' => [
			'[<lang [a-z]{2}>/]install/<presenter>/<action>',
		],],
		['IqrfNet:' => [
			'[<lang [a-z]{2}>/]iqrfnet/dpa-config/<address>',
			'[<lang [a-z]{2}>/]iqrfnet/enumeration/<address>',
			'[<lang [a-z]{2}>/]iqrfnet/os-config/<address>',
			'[<lang [a-z]{2}>/]iqrfnet/tr-security/<address>',
			'[<lang [a-z]{2}>/]iqrfnet/<presenter>/<action>',
		],],
		['Service:' => [
			'[<lang [a-z]{2}>/]service/<presenter>/<action>',
		],],
		['Core:' =>
			['[<lang [a-z]{2}>/]<presenter>/<action>[/<id>]'],
		],
	];

	/**
	 * Tests the function to create a router
	 */
	public function testCreateRouter(): void {
		/** @var RouteList $routeList */
		$routeList = RouterFactory::createRouter();
		Assert::type(RouteList::class, $routeList);
		Assert::null($routeList->getModule());
		Assert::same($this->expected, array_map(function (IRouter $type) {
			if ($type instanceof Route) {
				return $type->getMask();
			} else {
				if ($type instanceof RouteList) {
					$routeMask = array_map(function (Route $route) {
						return $route->getMask();
					}, $type->getIterator()->getArrayCopy());
					return [$type->getModule() => $routeMask];
				}
			}
			return;
		}, $routeList->getIterator()->getArrayCopy()));
	}

}

$test = new RouterFactoryTest();
$test->run();