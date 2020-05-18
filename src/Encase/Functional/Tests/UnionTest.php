<?php
namespace Encase\Functional\Tests;

use Encase\Functional\Collection;
use function Encase\Functional\union;

class UnionTest extends TestCase
{
	public function testWithArray()
	{
		$result = union([2], [1, 2]);
		$this->assertSame([2, 1], $result);

		$result = union([1, 2, 3], [4, 5, 6], [7, 8, 9]);
		$this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9], $result);

		$result = union([1, 2, 3], [4, 5, 6, 7, 8]);
		$this->assertSame([1, 2, 3, 4, 5, 6, 7, 8], $result);
	}

	public function testWithAssocArray()
	{
		$result = union(
			['a' => 1, 'c' => 3],
			['b' => 2, 'd' => 4],
			['e' => 5, 'a' => 6]
		);
		$this->assertSame([
			'a' => 6,
			'c' => 3,
			'b' => 2,
			'd' => 4,
			'e' => 5,
		], $result);
	}

	public function testWithAssocAndSequentialArray()
	{
		$result = union(
			['a', 'a' => 1, 'b', 'c' => 4],
			['b', 'c', 'b' => 2, 'c' => 3]
		);
		$this->assertSame([
			'a', 'b', 'c',
			'a' => 1,
			'c' => 3,
			'b' => 2,
		], $result);
	}

	public function testUnionWithArrayObject()
	{
		$obj1 = new \ArrayObject([1, 2, 3]);
		$obj2 = new \ArrayObject([2, 4, 6]);
		$result = union($obj1, $obj2);
		$this->assertSame([1, 2, 3, 4, 6], $result);
	}

	public function testUnionWithGenerator()
	{
		$result = union([0, 2, 4], self::generator());
		$this->assertSame([0, 2, 4, 1, 3], $result);
	}

	public function testUnionCollectionMethod()
	{
		$coll1 = new Collection(1, 2, 3, 4);
		$coll2 = new Collection(2, 4, 6, 8);
		$result = $coll1->union($coll2);
		$this->assertEquals(new Collection(1, 2, 3, 4, 6, 8), $result);
	}

	public static function generator()
	{
		for ($i = 1; $i <= 3; ++$i) {
			yield $i;
		}
	}
}
