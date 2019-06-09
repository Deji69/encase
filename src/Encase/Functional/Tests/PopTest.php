<?php
namespace Encase\Functional\Tests;

use Encase\Tests\TestCase;
use Encase\Functional\Collection;
use function Encase\Functional\pop;

class PopTest extends TestCase
{
	public function testPopArray()
	{
		$array = ['a', 'b', 'c'];
		$removed = pop($array);
		$this->assertSame('c', $removed);
		$this->assertSame(['a', 'b'], $array);
	}

	public function testPopAssociativeArray()
	{
		$array = ['a' => 1, 'b' => 2, 'c' => 3];
		$removed = pop($array);
		$this->assertSame(3, $removed);
		$this->assertSame(['a' => 1, 'b' => 2], $array);
	}

	public function testPopString()
	{
		$string = 'hello';
		$removed = pop($string);
		$this->assertSame('o', $removed);
		$this->assertSame('hell', $string);
	}

	public function testPopStdClass()
	{
		$object = (object)['a' => 1, 'b' => 2, 'c' => 3];
		$removed = pop($object);
		$this->assertSame(3, $removed);
		$this->assertSame(['a' => 1, 'b' => 2], (array)$object);
	}

	public function testPopArrayObject()
	{
		$object = new \ArrayObject(['a' => 1, 'b' => 2, 'c' => 3]);
		$removed = pop($object);
		$this->assertSame(3, $removed);
		$this->assertSame(['a' => 1, 'b' => 2], (array)$object);
	}

	public function testPopCollection()
	{
		$coll = new Collection(1, 2, 3);
		$removed = pop($coll);
		$this->assertSame(3, $removed);
		$this->assertSame([0 => 1, 1 => 2], $coll->all());
	}

	public function testPopIterator()
	{
		$object = new \ArrayObject(['a' => 1, 'b' => 2, 'c' => 3]);
		$iterator = $object->getIterator();
		$removed = pop($iterator);
		$this->assertSame(3, $removed);
		$this->assertSame(['a' => 1, 'b' => 2], (array)$object);
		$this->assertSame(['a' => 1, 'b' => 2], $iterator->getArrayCopy());
	}
}
