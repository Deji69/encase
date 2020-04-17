<?php
namespace Encase\Functional\Tests;

use function Encase\Functional\isIntKeyedArray;

class IsIntKeyedArrayTest extends TestCase
{
	/** @dataProvider  casesNonArray */
	public function testWithNonArray($value)
	{
		$this->assertFalse(isIntKeyedArray($value));
	}

	public function testWithEmptyArray()
	{
		$this->assertFalse(isIntKeyedArray([]));
	}

	public function testWithSequentialArray()
	{
		$this->assertTrue(isIntKeyedArray([0, 50, 100]));
	}

	public function testWithIndexedArray()
	{
		$this->assertTrue(isIntKeyedArray([2 => 0, 0 => 50, 1 => 100]));
	}

	public function testWithStringKeyedArray()
	{
		$this->assertFalse(isIntKeyedArray(['a' => 0, 'b' => 50, 'c' => 100]));
	}

	public function testWithIndexKeyedArray()
	{
		$this->assertTrue(isIntKeyedArray([3 => 0, 2 => 1, 1 => 2]));
	}

	public function testWithIndexStringKeyedArray()
	{
		$this->assertTrue(isIntKeyedArray(['1' => 0, '0' => 1, '2' => 2]));
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
