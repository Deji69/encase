<?php
namespace Encase\Functional\Tests;

use function Encase\Functional\isSequentialArray;

class IsSequentialArrayTest extends TestCase
{
	/** @dataProvider  casesNonArray */
	public function testWithNonArray($value)
	{
		$this->assertFalse(isSequentialArray($value));
	}

	public function testWithEmptyArray()
	{
		$this->assertTrue(isSequentialArray([]));
	}

	public function testWithSequentialArray()
	{
		$this->assertTrue(isSequentialArray([0, 50, 100]));
	}

	public function testWithIndexedArray()
	{
		$this->assertFalse(isSequentialArray([2 => 0, 0 => 50, 1 => 100]));
	}

	public function testWithStringKeyedArray()
	{
		$this->assertFalse(isSequentialArray([0, 50, 'a' => 100]));
	}

	public function testWithIndexKeyedArray()
	{
		$this->assertFalse(isSequentialArray([3 => 0, 2 => 1, 1 => 2]));
	}

	public function casesNonArray()
	{
		return [
			[null],
			[false],
			[true],
			[13],
			['blah'],
			[(object)[]],
		];
	}
}
