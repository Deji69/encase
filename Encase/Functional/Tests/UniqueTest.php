<?php
namespace Encase\Functional\Tests;

use Encase\Functional\Collection;
use function Encase\Functional\unique;

class UniqueTest extends TestCase
{
	public function testUniqueArray()
	{
		$array = [1, 2, 1, 3, 3];
		$result = unique($array);
		$this->assertSame([1, 2, 3 => 3], $result);
	}

	public function testUniqueAssocArray()
	{
		$array = [1, 2, 'a' => 1, 'b' => 3, 3];
		$result = unique($array);
		$this->assertSame([1, 2, 'b' => 3], $result);
	}

	public function testUniqueAssocArrayKeepKeyed()
	{
		$array = [1, 2, 'a' => 1, 'b' => 3, 3];
		$result = unique($array, true);
		$this->assertSame([1, 2, 3, 'a' => 1, 'b' => 3], $result);
	}

	public function testUniqueArrayObject()
	{
		$obj = new \ArrayObject([1, 2, 1, 3, 3]);
		$result = unique($obj);
		$this->assertSame([1, 2, 3 => 3], $result);
	}

	public function testUniqueGenerator()
	{
		$result = unique(self::generator());
		$this->assertSame([0 => 1, 2 => 2, 4 => 3], $result);
	}

	public function testUniqueStdClass()
	{
		$obj = new \stdClass;
		$obj->a = 1;
		$obj->b = 2;
		$obj->c = 3;
		$obj->d = 2;
		$result = unique($obj);
		$this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $result);
	}

	public function testUniqueCollectionMethod()
	{
		$coll = new Collection(1, 2, 3, 2);
		$result = $coll->unique();
		$this->assertEquals(new Collection(1, 2, 3), $result);
	}

	public static function generator()
	{
		for ($i = 1; $i <= 3; ++$i) {
			yield $i;
			yield $i;
		}
	}
}
