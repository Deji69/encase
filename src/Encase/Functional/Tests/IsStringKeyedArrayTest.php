<?php
namespace Encase\Functional\Tests;

use function Encase\Functional\isStringKeyedArray;

class IsStringKeyedArrayTest extends TestCase
{
	/** @dataProvider  casesNonArray */
	public function testWithNonArray($value)
	{
		$this->assertFalse(isStringKeyedArray($value));
	}

	public function testWithEmptyArray()
	{
		$this->assertFalse(isStringKeyedArray([]));
	}

	public function testWithSequentialArray()
	{
		$this->assertFalse(isStringKeyedArray([0, 50, 100]));
	}

	public function testWithIndexedArray()
	{
		$this->assertFalse(isStringKeyedArray([2 => 0, 0 => 50, 1 => 100]));
	}

	public function testWithStringKeyedArray()
	{
		$this->assertTrue(isStringKeyedArray([0, 50, 'a' => 100]));
	}

	public function testWithIndexKeyedArray()
	{
		$this->assertFalse(isStringKeyedArray([3 => 0, 2 => 1, 1 => 2]));
	}

	public function testWithIndexStringKeyedArray()
	{
		$this->assertFalse(isStringKeyedArray(['1' => 0, '0' => 1, '2' => 2]));
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
