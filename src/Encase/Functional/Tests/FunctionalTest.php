<?php
namespace Encase\Functional\Tests;

use BadMethodCallException;
use Encase\Functional\Tests\TestCase;
use Encase\Functional\Traits\Functional;

function foo() { return 'foo'; }
function bar() { return 'bar'; }

class FunctionalTest extends TestCase
{
	public function testBoxing()
	{
		$test = TestClassWithValue::box(123);
		$this->assertInstanceOf(TestClassWithValue::class, $test);
		$this->assertSame($test->value, 123);
	}

	public function testBoxingWithCast()
	{
		$test = TestClassWithValueCasting::box('123');
		$this->assertInstanceOf(TestClassWithValue::class, $test);
		$this->assertSame($test->value, 123);
	}

	public function testNamespaceCanBeIncluded()
	{
		$this->expectNotToPerformAssertions();

		$object = new class {
			use Functional;

			private static function getMethodFunctionNamespaces(): array
			{
				return ['Encase\\Functional\\Tests\\'];
			}
		};

		$object->foo();
		$object->bar();
	}

	public function testIncludedMethodCanBeMadeStatic()
	{
		$object = TestClassWithStaticMethod::foo();
		$this->assertInstanceOf(TestClassWithStaticMethod::class, $object);
		$this->assertSame('bar', $object->bar());
	}

	public function testIncludedMethodWithBoxedReturnCannotBeCalledNonStatically()
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Method Encase\\Functional\\Tests\\TestClassWithStaticMethod::foo does not exist');
		$object = TestClassWithStaticMethod::foo();
		$object->foo();
	}

	public function testFunctionCanBeExcluded()
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Method Encase\\Functional\\Tests\\TestClassWithBarFunctionExcluded::bar does not exist');
		$object = new TestClassWithBarFunctionExcluded();
		$object->foo();
		$object->bar();
	}
}

class TestClassWithValue
{
	use Functional;

	public $value;

	public function __construct($value)
	{
		$this->value = $value;
	}
}

class TestClassWithValueCasting extends TestClassWithValue
{
	public static function cast($value)
	{
		if (\is_string($value)) {
			return +$value;
		}
		return $value;
	}
}

class TestClassWithBarFunctionExcluded
{
	use Functional;

	private static function getMethodFunctionNamespaces(): array
	{
		return ['Encase\\Functional\\Tests\\'];
	}

	private static function getFunctionsToExcludeAsMethodCalls(): array
	{
		return ['Encase\\Functional\\Tests\\bar'];
	}
}

class TestClassWithStaticMethod
{
	use Functional;

	private static function getMethodFunctionNamespaces(): array
	{
		return ['Encase\\Functional\\Tests\\'];
	}

	private static function getStaticMethodsWithBoxedReturns(): array
	{
		return ['foo'];
	}
}
