<?php
namespace Encase\Functional\Tests;

use Encase\Functional\Collection;
use function Encase\Functional\shift;

class ShiftTest extends TestCase
{
	public function testShiftArray()
	{
		$array = ['a', 'b', 'c'];
		$removed = shift($array);
		$this->assertSame('a', $removed);
		$this->assertSame(['b', 'c'], $array);
	}

	public function testShiftAssociativeArray()
	{
		$array = ['a' => 1, 'b' => 2, 'c' => 3];
		$removed = shift($array);
		$this->assertSame(1, $removed);
		$this->assertSame(['b' => 2, 'c' => 3], $array);
	}

	public function testShiftString()
	{
		$string = 'hello';
		$removed = shift($string);
		$this->assertSame('h', $removed);
		$this->assertSame('ello', $string);
	}

	public function testShiftStdClass()
	{
		$object = (object)['a' => 1, 'b' => 2, 'c' => 3];
		$removed = shift($object);
		$this->assertSame(1, $removed);
		$this->assertSame(['b' => 2, 'c' => 3], (array)$object);
	}

	public function testShiftArrayObject()
	{
		$object = new \ArrayObject(['a' => 1, 'b' => 2, 'c' => 3]);
		$removed = shift($object);
		$this->assertSame(1, $removed);
		$this->assertSame(['b' => 2, 'c' => 3], (array)$object);
	}

	public function testShiftCollection()
	{
		$coll = new Collection(1, 2, 3);
		$removed = shift($coll);
		$this->assertSame(1, $removed);
		$this->assertSame([1 => 2, 2 => 3], $coll->all());
	}

	public function testShiftIterator()
	{
		$object = new \ArrayObject(['a' => 1, 'b' => 2, 'c' => 3]);
		$iterator = $object->getIterator();
		$removed = shift($iterator);
		$this->assertSame(1, $removed);
		$this->assertSame(['b' => 2, 'c' => 3], (array)$object);
		$this->assertSame(['b' => 2, 'c' => 3], $iterator->getArrayCopy());
	}
}
