<?php
namespace Encase\Functional\Tests;

use function Encase\Functional\isIndexedArray;

class IsIndexedArrayTest extends TestCase
{
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
}
