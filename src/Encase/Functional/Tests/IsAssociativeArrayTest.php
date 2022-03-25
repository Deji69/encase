<?php
namespace Encase\Functional\Tests;

use function Encase\Functional\isAssociativeArray;

class IsAssociativeArrayTest extends TestCase
{
	/** @dataProvider  casesNonArray */
	public function testWithNonArray($value)
	{
		$this->assertFalse(isAssociativeArray($value));
	}

	public function testWithEmptyArray()
	{
		$this->assertTrue(isAssociativeArray([]));
	}

	public function testWithSequentialArray()
	{
		$this->assertFalse(isAssociativeArray([0, 50, 100]));
	}

	public function testWithIndexedArray()
	{
		$this->assertTrue(isAssociativeArray([2 => 0, 0 => 50, 1 => 100]));
	}

	public function testWithStringKeyedArray()
	{
		$this->assertTrue(isAssociativeArray([0, 50, 'a' => 100]));
	}

	public function testWithIndexKeyedArray()
	{
		$this->assertTrue(isAssociativeArray([3 => 0, 2 => 1, 1 => 2]));
	}

	public function testWithIndexStringKeyedArray()
	{
		$this->assertTrue(isAssociativeArray(['1' => 0, '0' => 1, '2' => 2]));
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
