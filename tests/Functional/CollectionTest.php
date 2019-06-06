<?php
namespace Tests\Functional;

use Encase\Functional\Str;
use Encase\Tests\TestCase;
use Encase\Functional\Number;
use Encase\Functional\Collection;

class CollectionTest extends TestCase
{
	/**
	 * @dataProvider casesConstruction
	 */
	public function testConstruction($value, $expect)
	{
		$collection = Collection::make(...$value);
		$this->assertSame($expect, $collection->all());
	}

	public function testFunctionalMethodProxying()
	{
		$collection = Collection::make(1, 2, 3);

		$result = $collection->map(function ($value, $key) {
			return $value * 2;
		}, true);

		$this->assertInstanceOf(Collection::class, $result);
		$this->assertNotSame($collection, $result);
		$this->assertSame([2, 4, 6], $result->all());
	}

	public function testSizeMethod()
	{
		$collection = Collection::make(1, 2, 3);
		$this->assertSame(3, $collection->size());
	}

	public function testAllMethod()
	{
		$collection = Collection::make(1, 2, 3);
		$this->assertSame([1, 2, 3], $collection->all());
	}

	public function testFindMethod()
	{
		$collection = Collection::make(1, 3, 5, 7, 9, 10, 11, 13, 15);
		$result = $collection->find(10);
		$this->assertSame([5, 10], $result);

		$result = $collection->find(function ($value) {
			return ($value % 2) === 0;
		});
		$this->assertSame([5, 10], $result);
	}

	public function testGetMethod()
	{
		$collection = Collection::make([1, 2, 3]);
		$this->assertSame(2, $collection->get(1));

		$collection = Collection::make([
			'a' => 1,
			'b' => 2,
			'c' => 3
		]);
		$this->assertSame(2, $collection->get('b'));
	}

	public function testIsEmptyMethod()
	{
		$collect = Collection::make();
		$this->assertTrue($collect->isEmpty());
		$collect = Collection::make(1);
		$this->assertTrue(!$collect->isEmpty());
	}

	public function testSliceMethod()
	{
		$collection = Collection::make(1, 2, 3, 4, 5);
		$result = $collection->slice(1, -1);
		$this->assertEquals(Collection::make([1 => 2, 2 => 3, 3 => 4]), $result);
		$result = $collection->slice(-1);
		$this->assertEquals(Collection::make([4 => 5]), $result);
	}

	public function testPushMethod()
	{
		$collection = Collection::make();
		$collection->push(1);
		$collection->push(2);
		$collection->push(3);
		$this->assertSame([1, 2, 3], $collection->all());
	}

	public function testPopMethod()
	{
		$collection = Collection::make(1, 2, 3, 4, 5);
		$res1 = $collection->pop();
		$res2 = $collection->pop();
		$this->assertSame([1, 2, 3], $collection->all());
		$this->assertSame(5, $res1);
		$this->assertSame(4, $res2);
	}

	public function testShiftMethod()
	{
		$collection = Collection::make(1, 2, 3, 4, 5);
		$result = $collection->shift();
		$this->assertSame(1, $result);
		$result = $collection->shift();
		$this->assertSame(2, $result);
		$this->assertSame([3, 4, 5], $collection->all());
	}

	/** @dataProvider casesEachMethodBoxesValues */
	public function testEachMethodValueIterator($value, $class)
	{
		$collection = Collection::make([$value]);
		$collection->getBoxIterator()->each(function ($value) use ($class) {
			$this->assertInstanceOf($class, $value);
		});
	}

	public function casesEachMethodBoxesValues()
	{
		return [
			'With integer' => [123, Number::class],
			'With float' => [3.14, Number::class],
			'With string' => ['hi', Str::class],
		];
	}

	public function casesConstruction()
	{
		$obj = (object)[];
		return [
			// Single parameter, value should be interpreted to an array.
			'With null' => [
				'args' => [null],
				'expect' => [],
			],
			'With integer' => [
				'args' => [3],
				'expect' => [null, null, null],
			],
			'With string' => [
				'args' => ['1234'],
				'expect' => ['1', '2', '3', '4'],
			],
			'With unicode string' => [
				'args' => ['test ✔'],
				'expect' => ['t', 'e', 's', 't', ' ', '✔'],
			],
			'With empty array' => [
				'args' => [[]],
				'expect' => [],
			],
			'With array with size of 1' => [
				'args' => [[3]],
				'expect' => [3],
			],
			'With longer array' => [
				'args' => [[3, 2, 1]],
				'expect' => [3, 2, 1],
			],
			'With associative array' => [
				'args' => [['a' => 1, 'b' => 2, 'c' => 3]],
				'expect' => ['a' => 1, 'b' => 2, 'c' => 3],
			],
			'With empty object' => [
				'args' => [$obj],
				'expect' => [],
			],
			'With object' => [
				'args' => [(object)['a' => 1, 'b' => 2]],
				'expect' => ['a' => 1, 'b' => 2],
			],

			// Multiple parameters, values should be used verbatim.
			'With multiple integers' => [
				'value' => [1, 2, 3],
				'expect' => [1, 2, 3],
			],
			'With multiple strings' => [
				'value' => ['123', 'foo', 'bar'],
				'expect' => ['123', 'foo', 'bar']
			],
			'With multiple arrays' => [
				'args' => [[3], [2], [1]],
				'expect' => [[3], [2], [1]],
			],
			'With multiple associative arrays' => [
				'args' => [['a' => 3], ['b' => 2], ['c' => 1]],
				'expect' => [['a' => 3], ['b' => 2], ['c' => 1]],
			],
			'With mixed values' => [
				'args' => [1, '2', 'three', [4], null, $obj],
				'expect' => [1, '2', 'three', [4], null, $obj],
			],
		];
	}
}
