<?php
namespace Encase\Functional\Tests;

use Encase\Functional\Value;

class ValueTest extends TestCase
{
	/**
	 * @dataProvider casesConstruction
	 */
	public function testConstruction($value)
	{
		$val = Value::new($value);
		$this->assertSame($value, $val->get());
	}

	/**
	 * @dataProvider casesConstruction
	 */
	public function testIsTypeProxiedFunctionalMethod($value, $type)
	{
		$val = Value::new($value);
		$this->assertSame($type, $val->isType($type));
	}

	public function testFunctionalMethodReturningInstanceProxying()
	{
		$value = Value::new([1, 2, 3]);
		$sum = 0;

		$value->each(function ($val) use (&$sum) {
			$sum += $val;
		});

		$this->assertSame(6, $sum);
	}

	public function testIsMethod()
	{
		$this->assertTrue(Value::new(123)->is(123));
		$this->assertFalse(Value::new(123)->is('123'));
	}

	public function testEqualsMethod()
	{
		$this->assertTrue(Value::new(123)->equals('123'));
		$this->assertFalse(Value::new('123')->equals('-123'));
	}

	public function testSizeMethod()
	{
		$value = Value::new([1, 2, 3]);
		$size = $value->size();

		$this->assertSame(3, $size);
	}

	public function testSliceMethod()
	{
		$value = Value::new('foo bar');
		$result = $value->slice(0, 3);
		$this->assertInstanceOf(Value::class, $result);
		$this->assertSame('foo', $result->get());
		$value = Value::new([1, 2, 3]);
		$this->assertSame([1, 2], $value->slice(0, 2)->get());
	}

	public function testShiftMethod()
	{
		$value = Value::new([1, 2, 3]);
		$result = $value->shift();
		$this->assertSame(1, $result);
		$this->assertSame([2, 3], $value->get());
	}

	public function testPopMethod()
	{
		$value = Value::new([1, 2, 3]);
		$result = $value->pop();
		$this->assertSame(3, $result);
		$this->assertSame([1, 2], $value->get());
	}

	public function testConcatMethod()
	{
		$value = Value::new('foo ');
		$result = $value->concat('bar');
		$this->assertInstanceOf(Value::class, $result);
		$this->assertNotSame($value, $result);
		$this->assertSame('foo bar', $result->get());

		$value = Value::new([1, 2, 3]);
		$result = $value->concat(4, 5);
		$this->assertSame([1, 2, 3, 4, 5], $result->get());
	}

	public function testConcatMethodWithAnotherValue()
	{
		$value = Value::new('foo ');
		$another = Value::new('bar');
		$result = $value->concat($another);
		$this->assertInstanceOf(Value::class, $result);
		$this->assertNotSame($value, $result);
		$this->assertNotSame($another, $result);
		$this->assertSame('foo bar', $result->get());
	}

	public function testCountable()
	{
		$value = Value::new([1, 2, 3]);
		$this->assertSame(3, count($value));
	}

	public function testIterable()
	{
		$value = Value::new([1, 2, 3]);
		$expect = 1;

		foreach ($value as $key => $val) {
			$this->assertSame($expect++, $val);
		}
	}

	public function testBoxedIterable()
	{
		$value = Value::new([1, 2, 3]);
		$sum = 0;
		$keys = 0;

		foreach ($value->boxIt() as $key => $val) {
			$this->assertInstanceOf(Value::class, $val);

			$sum += $val->get();
			$keys += $key;
		}

		$this->assertSame(6, $sum);
		$this->assertSame(3, $keys);
	}

	public function testContainedInstancesAreSame()
	{
		$obj1 = (object)['a' => 1, 'b' => 2];

		$value = Value::new($obj1);
		$this->assertSame($obj1, $value->get());

		$value = Value::new([$obj1]);
		$this->assertSame($obj1, $value[0]->get());
	}

	public function testInvokable()
	{
		$called = false;

		$value = Value::new(function () use (&$called) {
			$called = true;
		});

		$value();
		$this->assertTrue($called);
	}

	public function casesConstruction()
	{
		$obj = (object)[];
		return [
			// Single parameter, value should be interpreted to an array.
			'With null' => [
				'value' => null,
				'type' => 'null',
			],
			'With integer' => [
				'value' => 3,
				'type' => 'integer',
			],
			'With string' => [
				'value' => '1234',
				'type' => 'string'
			],
			'With empty array' => [
				'value' => [],
				'type' => 'array',
			],
			'With array with size of 1' => [
				'value' => [1],
				'type' => 'array',
			],
			'With associative array' => [
				'value' => ['a' => 1, 'b' => 2, 'c' => 3],
				'type' => 'array',
			],
			'With empty object' => [
				'value' => $obj,
				'type' => 'object',
			],
			'With object' => [
				'value' => (object)['a' => 1, 'b' => 2],
				'type' => 'object',
			],
		];
	}
}
