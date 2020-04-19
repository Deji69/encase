<?php
namespace Encase\Functional\Tests;

use BadMethodCallException;
use Encase\Functional\Tests\TestCase;
use Encase\Functional\Traits\Functional;

function foo() {}
function bar() {}

class FunctionalTest extends TestCase
{
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
		$object->bar();
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

	private static function getStaticMethodNames(): array
	{
		return ['foo'];
	}
}
