<?php

/**
 * TEST: App\IqrfNetModule\Parsers\EnumerationParser
 * @covers App\IqrfNetModule\Parsers\EnumerationParser
 * @phpVersion >= 7.0
 * @testCase
 */
declare(strict_types = 1);

namespace Test\IqrfNetModule\Models;

use App\CoreModule\Models\JsonFileManager;
use App\IqrfNetModule\Parsers\EnumerationParser;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * Tests for parser of DPA Enumeration responses
 */
class EnumerationParserTest extends TestCase {

	/**
	 * @var Container Nette Tester Container
	 */
	private $container;

	/**
	 * @var EnumerationParser DPA Enumeration response parser
	 */
	private $parser;

	/**
	 * @var string Enumeration packet
	 */
	private $packet;

	/**
	 * @var array Expected Enumeration parsed response
	 */
	private $expected;

	/**
	 * Constructor
	 * @param Container $container Nette Tester Container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		$this->parser = new EnumerationParser();
		$jsonFileManager = new JsonFileManager(__DIR__ . '/../../data/iqrf/');
		$this->packet = $jsonFileManager->read('response-enumeration')['data']['rsp']['rData'];
		$this->expected = $jsonFileManager->read('data-enumeration');
	}

	/**
	 * Test function to parse DPA response
	 */
	public function testParse(): void {
		Assert::equal($this->expected, $this->parser->parse($this->packet));
	}

	/**
	 * Test function to parse response to DPA Enumeration request
	 */
	public function testParsePeripheralEnumeration(): void {
		$actual = $this->parser->parsePeripheralEnumeration($this->packet);
		Assert::equal($this->expected, $actual);
	}

	/**
	 * Test function to get embedded peripherals
	 */
	public function testGetEmbeddedPers(): void {
		$actual = $this->parser->getEmbeddedPers($this->packet);
		Assert::equal($this->expected['EmbeddedPers'], $actual);
	}

}

$test = new EnumerationParserTest($container);
$test->run();