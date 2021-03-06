<?php
namespace Encase\Functional\Tests;

use function Encase\Functional\isIndexedArray;

class IsIndexedArrayTest extends TestCase
{
	/** @dataProvider  casesNonArray */
	public function testWithNonArray($value)
	{
		$this->assertFalse(isIndexedArray($value));
	}

	public function testWithEmptyArray()
	{
		$this->assertTrue(isIndexedArray([]));
	}

	public function testWithSequentialArray()
	{
		$this->assertTrue(isIndexedArray([0, 50, 100]));
	}

	public function testWithIndexedArray()
	{
		$this->assertTrue(isIndexedArray([2 => 0, 0 => 50, 1 => 100]));
	}

	public function testWithStringKeyedArray()
	{
		$this->assertFalse(isIndexedArray([0, 50, 'a' => 100]));
	}

	public function testWithIndexKeyedArray()
	{
		$this->assertFalse(isIndexedArray([3 => 0, 2 => 1, 1 => 2]));
	}

	public function testWithIndexStringKeyedArray()
	{
		$this->assertTrue(isIndexedArray(['1' => 0, '0' => 1, '2' => 2]));
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
