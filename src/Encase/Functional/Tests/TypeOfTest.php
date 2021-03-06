<?php
namespace Encase\Functional\Tests;

use function Encase\Functional\typeOf;

class TypeOfTest extends TestCase
{
	/** @dataProvider casesBasic */
	public function testBasic($value, $type)
	{
		$this->assertSame(typeOf($value), $type);
	}

	public function casesBasic()
	{
		return [
			'With null' => [null, 'null'],
			'With integer' => [3, 'int'],
			'With float' => [3.14, 'float'],
			'With string' => ['foo', 'string'],
			'With array' => [[1], 'array'],
			'With object' => [(object)['a' => 1], 'object'],
			'With resource' => [fopen('php://input', 'r'), 'resource'],
		];
	}
}
