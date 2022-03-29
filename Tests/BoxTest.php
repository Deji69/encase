<?php
namespace Encase\Functional\Tests;

use Encase\Functional\Str;
use Encase\Functional\Func;
use Encase\Functional\Value;
use Encase\Functional\Number;
use Encase\Functional\Collection;
use function Encase\Functional\box;
use Encase\Functional\Exceptions\InvalidTypeError;

class BoxTest extends TestCase
{
	public function testBoxing()
	{
		$str = box('test');
		$this->assertSame(Str::class, \get_class($str));
		$this->assertSame('test', $str->get());

		$value = box(123);
		$this->assertSame(Number::class, \get_class($value));
		$this->assertSame(123, $value->get());

		$value = box(function () {});
		$this->assertSame(Func::class, \get_class($value));
	}

	public function testCannotDoubleBox() {
		$value = box(new Number(123));
		$this->assertSame(Number::class, \get_class($value));
		$this->assertSame(123, $value->get());

		$str = box(new Str('hello'));
		$this->assertSame(Str::class, \get_class($str));
		$this->assertSame('hello', $str->get());
	}

	public function testBoxedObjectIsCloned()
	{
		$object = new \stdClass(['a' => 1, 'b' => 2, 'c' => 3]);
		$boxed = box($object);
		$this->assertNotSame($object, $boxed->get());
		$this->assertEquals($object, $boxed->get());
	}

	public function testBoxWithTypeHintInvalidValueThrowsException()
	{
		$this->expectException(\InvalidArgumentException::class);
		Func::box(1);
	}

	public function testValueConversion()
	{
		$str = Str::box(123);
		$this->assertSame(Str::class, \get_class($str));
		$this->assertSame('123', $str->get());
	}

	public function testBoxedValueConversion()
	{
		$value = box(123);
		$this->assertSame(Number::class, \get_class($value));
		$value = Str::box($value);
		$this->assertSame(Str::class, \get_class($value));
		$this->assertSame('123', $value->get());
	}

	public function testStringToNumberConversion()
	{
		$value = Number::box('123');
		$this->assertSame(123, $value->get());
		$value = Number::box('3.14');
		$this->assertSame(3.14, $value->get());
	}

	public function testInvalidStringToNumberConversionThrowsException()
	{
		$this->expectException(InvalidTypeError::class);
		$this->expectExceptionMessage('expects numeric, string given');
		Number::box('abc');
	}

	/** @dataProvider caseAllValidBoxes */
	public function testAllValidBoxes($input, string $class)
	{
		$result = box($input);
		$this->assertSame($class, \get_class($result));
	}

	public function caseAllValidBoxes()
	{
		return [
			'With integer' => [123, Number::class],
			'With float' => [3.14, Number::class],
			'With string' => ['123', Str::class],
			'With Closure' => [function () { }, Func::class],
			'With array' => [[1, 2, 3], Collection::class],
			'With object' => [(object)['a' => 1, 'b' => 2, 'c' => 3], Value::class],
			'With iterable' => [new \ArrayObject(['a' => 1, 'b' => 2, 'c' => 3]), Value::class],
		];
	}

	/** @dataProvider caseAllValidTypeHints */
	public function testAllValidTypeHints($input, string $class)
	{
		$result = $class::box($input);
		$this->assertSame($class, \get_class($result));
		$this->assertEquals($input, $result->get());
	}

	public function caseAllValidTypeHints()
	{
		return [
			'With string to Number (int)' => ['123', Number::class],
			'With string to Number (float)' => ['3.14', Number::class],
			'With integer to Str' => [123, Str::class],
			'With float to Str' => [3.14, Str::class],
			'With callable to Func' => ['is_callable', Func::class],
		];
	}
}
