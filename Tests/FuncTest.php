<?php
namespace Encase\Functional\Tests;

use Encase\Functional\Func;
use function Encase\Functional\box;

class FuncTest extends TestCase
{
	public function testCanCallInternalFunctions()
	{
		$strToUpper = new Func('strtoupper');
		$result = $strToUpper("hello");
		$this->assertSame("HELLO", $result);
	}

	public function testCanCallClosures()
	{
		$addOne = new Func(function ($value) {
			return $value + 1;
		});
		$result = $addOne(1);
		$this->assertSame(2, $result);
	}

	public function testCanCallMethods()
	{
		$addOne = new Func([$this, 'mockMethod']);
		$result = $addOne(1);
		$this->assertSame(2, $result);
	}

	public function testCanCallStaticMethods()
	{
		$addOne = new Func([self::class, 'mockStaticMethod']);
		$result = $addOne(1);
		$this->assertSame(2, $result);
	}

	public function testCanCheckIfClosure()
	{
		$func = new Func('strtoupper');
		$this->assertFalse($func->isClosure());
		$func = new Func([$this, 'mockMethod']);
		$this->assertFalse($func->isClosure());
		$func = new Func([self::class, 'mockStaticMethod']);
		$this->assertFalse($func->isClosure());
		$func = new Func(function () {});
		$this->assertTrue($func->isClosure());
	}

	public function testCanCheckIfInternal()
	{
		$func = new Func('strtoupper');
		$this->assertTrue($func->isInternal());
		$func = new Func([$this, 'mockMethod']);
		$this->assertFalse($func->isInternal());
		$func = new Func([self::class, 'mockStaticMethod']);
		$this->assertFalse($func->isInternal());
		$func = new Func(function () {});
		$this->assertFalse($func->isInternal());
	}

	public function testCanCheckIfMethod()
	{
		$func = new Func('strtoupper');
		$this->assertFalse($func->isMethod());
		$func = new Func([$this, 'mockMethod']);
		$this->assertTrue($func->isMethod());
		$func = new Func([self::class, 'mockStaticMethod']);
		$this->assertTrue($func->isMethod());
		$func = new Func(function () {});
		$this->assertFalse($func->isMethod());
	}

	public function testCanCheckIfVariadic()
	{
		$func = new Func([$this, 'mockVariadicMethod']);
		$this->assertTrue($func->isVariadic());
		$func = new Func(function (...$params) {});
		$this->assertTrue($func->isVariadic());
		$func = new Func([$this, 'mockMethod']);
		$this->assertFalse($func->isVariadic());
		$func = new Func(function ($params) {});
		$this->assertFalse($func->isVariadic());
	}

	public function testCanGetNumberOfParameters()
	{
		$func = new Func(function ($one, $two) {});
		$this->assertSame(2, $func->getNumberOfParameters());
	}

	public function testCanGetNumberOfRequiredParameters()
	{
		$func = new Func(function ($one, $two) {});
		$this->assertSame(2, $func->getNumberOfRequiredParameters());
		$func = new Func(function ($one, $two = null) {});
		$this->assertSame(1, $func->getNumberOfRequiredParameters());
	}

	public function testCanGetReflection()
	{
		$reflection = new \ReflectionFunction('strtoupper');
		$func = new Func('strtoupper');
		$this->assertEquals($reflection, $func->getReflection());
		$reflection = new \ReflectionMethod(self::class, 'mockStaticMethod');
		$func = new Func([self::class, 'mockStaticMethod']);
		$this->assertEquals($reflection, $func->getReflection());
	}

	public function testCanBoxFunction()
	{
		$func = box(function () {});
		$this->assertInstanceOf(Func::class, $func);
	}

	public function mockMethod(int $value): int
	{
		return $value + 1;
	}

	public function mockVariadicMethod(...$params): void
	{
	}

	public static function mockStaticMethod(int $value): int
	{
		return $value + 1;
	}
}
