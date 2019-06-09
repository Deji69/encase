<?php
namespace Encase\Functional\Tests;

use Mockery as m;
use Encase\Tests\TestCase;
use function Encase\Functional\map;

class MapTest extends TestCase
{
	/** @var \Mockery\MockInterface */
	protected $mock;

	protected function setUp(): void
	{
		parent::setUp();
		$this->mock = m::mock();
	}

	/**
	 * @dataProvider casesBasic
	 */
	public function testBasicPreserveKeys($value, $expect, $loose = false)
	{
		$call = $this->mockCall($value);
		$result = map($value, function ($val, $key, $iter) use ($value, $call) {
			$this->assertSame($value, $iter);
			return $call($val, $key, $iter);
		}, true);

		if ($loose) {
			$this->assertEquals($expect, $result);
		} else {
			$this->assertSame($expect, $result);
		}
	}

	/**
	 * @dataProvider casesBasic
	 */
	public function testBasicReindex($value, $expect, $loose = false)
	{
		$expect = \array_values((array)$expect);

		$call = $this->mockCall($value, true);
		$result = map($value, function ($val, $key, $iter) use ($value, $call) {
			$this->assertSame($value, $iter);
			return $call($val, $key, $iter);
		});

		if ($loose) {
			$this->assertEquals($expect, (array)$result);
		} else {
			$this->assertSame($expect, $result);
		}
	}

	public function testMapTrimArrayOfStrings()
	{
		$array = [
			'here', 'are ', ' some', ' strings '
		];
		$result = map($array, 'trim');
		$this->assertSame(['here', 'are', 'some', 'strings'], $result);
	}

	public function testClosure()
	{
		$values = ['a' => 1, 'b' => 2, 'c' => 3];
		$expect = ['a' => 'a', 'b' => 'b', 'c' => 'c'];

		$result = map($values, function ($value, $key) {
			return $key;
		}, true);

		$this->assertSame($expect, $result);
	}

	/**
	 * @dataProvider casesInvalidArgumentExceptions
	 */
	public function testTypeAssertions($value, $type)
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage(", $type given");
		map($value, $this->mockCall(null));
	}

	public function mockCall($values)
	{
		$mock = m::mock();

		if (empty($values)) {
			$mock->shouldNotReceive('call');
		} else {
			if ($values instanceof \ArrayIterator) {
				$array = $values->getArrayCopy();
			} else {
				$array = $values;
			}

			foreach ($array as $key => $value) {
				$mock->expects('call')
				     ->with($value, $key, $values)
				     ->once()
				     ->andReturnUsing(function ($value) {
				    	return $value * 2;
				     });
			}
		}

		return [$mock, 'call'];
	}

	public function casesBasic()
	{
		return [
			'With null' => [
				'value' => null,
				'expect' => [],
			],
			'With empty array' => [
				'value' => [],
				'expect' => [],
			],
			'With array' => [
				'value' => [1, 2, 3],
				'expect' => [2, 4, 6],
			],
			'With associative array' => [
				'value' => ['a' => 1, 'b' => 2, 'c' => 3],
				'expect' => ['a' => 2, 'b' => 4, 'c' => 6],
			],
			'With empty object' => [
				'value' => (object)[],
				'expect' => [],
			],
			'With object' => [
				'value' => (object)['a' => 1, 'b' => 2, 'c' => 3],
				'expect' => ['a' => 2, 'b' => 4, 'c' => 6],
			],
			'With empty ArrayObject' => [
				'value' => new \ArrayObject([]),
				'expect' => new \ArrayObject([]),
				'loose' => true,
			],
			'With ArrayObject' => [
				'value' => new \ArrayObject(['a' => 1, 'b' => 2, 'c' => 3]),
				'expect' => new \ArrayObject(['a' => 2, 'b' => 4, 'c' => 6]),
				'loose' => true,
			],
			'With Iterator' => [
				'value' => (new \ArrayObject(['a' => 1, 'b' => 2, 'c' => 3]))->getIterator(),
				'expect' => new \ArrayObject(['a' => 2, 'b' => 4, 'c' => 6]),
				'loose' => true,
			],
		];
	}

	public function casesInvalidArgumentExceptions()
	{
		return [
			'With empty string' => [
				'iterable' => '',
				'type' => 'string',
			],
			'With string' => [
				'iterable' => 'hello',
				'type' => 'string',
			],
			'With zero number' => [
				'iterable' => 0,
				'type' => 'integer',
			],
			'With number' => [
				'iterable' => 3.14,
				'type' => 'double',
			],
			'With DateTime' => [
				'iterable' => new \DateTime(),
				'type' => 'DateTime',
			],
		];
	}
}
