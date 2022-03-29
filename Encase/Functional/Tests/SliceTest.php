<?php
namespace Encase\Functional\Tests;

use function Encase\Functional\slice;

class SliceTest extends TestCase
{
	public function testSliceString()
	{
		$slice = slice('Hello world', 0, 5);
		$this->assertSame('Hello', $slice);
		$slice = slice('Hello world', null, -6);
		$this->assertSame('Hello', $slice);
		$slice = slice('Hello world', 6);
		$this->assertSame('world', $slice);
		$slice = slice('Hello world', 5, 1);
		$this->assertSame('ello', $slice);
		$slice = slice('Hello world', -5);
		$this->assertSame('world', $slice);
		$slice = slice('Hello world', -9, -6);
		$this->assertSame('llo', $slice);
		$slice = slice('Hello world', 0, 4);
		$this->assertSame('Hell', $slice);
		$slice = slice('Hello world', -6, -9);
		$this->assertSame('llo', $slice);
		$slice = slice('Hello world', 2, -2);
		$this->assertSame('llo wor', $slice);
	}

	public function testSliceArray()
	{
		$array = [1, 2, 3, 4, 5, 6];
		$slice = slice($array, 2, 5);
		$this->assertSame([2 => 3, 3 => 4, 4 => 5], $slice);
		$slice = slice($array, null, -4);
		$this->assertSame([1, 2], $slice);
		$slice = slice($array, 3);
		$this->assertSame([3 => 4, 4 => 5, 5 => 6], $slice);
		$slice = slice($array, -2);
		$this->assertSame([4 => 5, 5 => 6], $slice);
	}

	public function testSlicedArrayHasKeysPreserved()
	{
		$array = [2 => 1, 4 => 2, 6 => 3];
		$this->assertSame($array, slice($array, 0));
	}

	public function testSliceAssociativeArray()
	{
		$array = [
			'one' => 1,
			'two' => 2,
			'three' => 3,
			'four' => 4,
			'five' => 5,
		];
		$result = slice($array, 2, -1);
		$this->assertSame(['three' => 3, 'four' => 4], $result);
	}

	public function testSliceIterable()
	{
		$object = new \ArrayObject(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);
		$result = slice($object, 1, 3);
		$this->assertSame(['b' => 2, 'c' => 3], $result);
	}

	public function testSliceStdClass()
	{
		$object = (object)['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
		$result = slice($object, 1, 3);
		$this->assertSame(['b' => 2, 'c' => 3], $result);
	}

	public function testSliceStdClassPastEnd()
	{
		$object = (object)['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
		$result = slice($object, 2, 5);
		$this->assertSame(['c' => 3, 'd' => 4], $result);
	}
}
