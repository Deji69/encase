<?php
namespace Encase\Functional\Tests;

use Encase\Functional\Func;
use function Encase\Functional\isType;

class IsTypeTest extends TestCase
{
	/** @dataProvider casesBasic */
	public function testBasic($value, $type, $not = false)
	{
		$values = \is_null($value) ? [$value] : (array)$value;
		$types = (array)$type;

		foreach ($values as $value) {
			foreach ($types as $type) {
				if ($not) {
					$this->assertFalse(isType($value, $type) == true, "Failed to assert value is not type $type");
				} else {
					$this->assertTrue(isType($value, $type) == true, "Failed to assert value is type $type");
				}
			}
		}
	}

	public function casesBasic()
	{
		return [
			'Is null' => [null, 'null'],
			'Is bool' => [false, 'bool'],
			'Is int' => [3, ['int', 'long']],
			'Is float' => [3.14, ['float']],
			'Is numeric' => [[3, 3.14, '31'], 'numeric'],
			'Is scalar' => [[3, 3.14, '31', true], 'scalar'],
			'Is string' => ['foo', 'string'],
			'Is array' => [[[1, 2, 3]], ['array', 'countable', 'iterable']],
			'Is object' => [[(object)[1, 2, 3]], ['object', 'stdClass']],
			'Is resource' => [\fopen('php://input', 'r'), 'resource'],
			'Is callable' => [['is_null', [$this, 'casesBasic'], function () { }], 'callable'],
			'Is function' => [[new Func('is_null'), function () { }], 'function'],
			'Callable is not function' => [['is_null', [$this, 'casesBasic']], 'function', true],
			'Null is not' => [null, ['bool', 'int', 'float', 'numeric', 'scalar', 'string', 'array', 'object', 'resource', 'callable'], true],
			'Int is not' => [3, ['null', 'bool', 'float', 'string', 'array', 'object', 'resource', 'callable'], true],
			'Float is not' => [3.14, ['null', 'bool', 'int', 'string', 'array', 'object', 'resource', 'callable'], true],
		];
	}
}
