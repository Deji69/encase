<?php
namespace Encase\Functional\Tests;

use Encase\Tests\TestCase;
use function Encase\Functional\type;

class TypeTest extends TestCase
{
	/** @dataProvider casesBasic */
	public function testBasic($value, $type)
	{
		$this->assertSame(type($value), $type);
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
