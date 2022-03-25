<?php
namespace Encase\Functional\Tests;

use Encase\Functional\Type;

class TypeTest extends TestCase
{
	/** @dataProvider casesConstruction */
	public function testConstruction(string $typeIn, string $expectType, string $classIn = null, string $expectClass = null)
	{
		$type = new Type($typeIn, $classIn);
		$this->assertSame($expectType, $type->type);
		$this->assertSame($expectClass, $type->class);

		$type = Type::new($typeIn, $classIn);
		$this->assertSame($expectType, $type->type);
		$this->assertSame($expectClass, $type->class);

		$type = Type::{$typeIn}($classIn);
		$this->assertSame($expectType, $type->type);
		$this->assertSame($expectClass, $type->class);
	}

	/** @dataProvider casesConstruction */
	public function testToString(string $typeIn, string $expectType, string $classIn = null)
	{
		$type = new Type($typeIn, $classIn);
		$this->assertSame($expectType, (string)$type);
	}

	/** @dataProvider casesEquals */
	public function testEquals(bool $expect, Type $type, $checkType, string $checkClass = null)
	{
		$result = $type->equals($checkType, $checkClass);
		$this->assertSame($expect, $result);
	}

	/** @dataProvider casesCheck */
	public function testCheck(Type $type, array $valueKeys)
	{
		foreach ($this->dataValues() as $key => $values) {
			foreach ($values as $value) {
				$result = $type->check($value);

				if (\in_array($key, $valueKeys, true)) {
					$this->assertTrue($result, "$key should pass");
				} else {
					$this->assertFalse($result, "$key should not pass");
				}
			}
		}
	}

	/** @dataProvider casesConstructFromVar */
	public function testOf($valueSet, string $expectType, array $expectClasses = null)
	{
		$values = $this->dataValues();

		foreach ($values[$valueSet] as $k => $val) {
			$type = Type::of($val);

			$this->assertSame($expectType, $type->type);

			if ($expectClasses !== null) {
				$this->assertSame($expectClasses[$k], $type->class, "class should be $expectClasses[$k]");
			} else {
				$this->assertNull($type->class);
			}
		}
	}

	public function testAnnotate()
	{
		$this->assertSame('int(5)', Type::annotate(5));
		$this->assertSame('float(3.1)', Type::annotate(3.1));
		$this->assertSame('string(\'foobar\')', Type::annotate('foobar'));
		$this->assertSame('object(stdClass)', Type::annotate(new \stdClass(['foo' => 'bar'])));
		$this->assertSame('[1, 2, 3]', Type::annotate([1, 2, 3]));
		$this->assertSame('[1, 2, 3, ..., 7, 8, 9]', Type::annotate([1, 2, 3, 4, 5, 6, 7, 8, 9]));
		$this->assertSame('[\'foo\' => \'bar\']', Type::annotate(['foo' => 'bar']));
		$this->assertSame('[0 => 1, 3 => 2, 4 => 3]', Type::annotate([1, 3 => 2, 3]));
	}

	public function casesConstruction()
	{
		return [
			['array', 'array'],
			['bool', 'bool'],
			['float', 'float'],
			['int', 'int'],
			['null', 'null'],
			['object', 'object'],
			['resource', 'resource'],
			['string', 'string'],
			['object', 'object', '\stdClass', 'stdClass'],
			['\stdClass', 'object', null, 'stdClass'],
		];
	}

	public function casesEquals()
	{
		return [
			'true null' => [true, new Type('null'), 'null'],
			'true int' => [true, new Type('int'), 'int'],
			'true string' => [true, new Type('string'), 'string'],
			'true object' => [true, new Type('object'), 'object'],
			'true class' => [true, new Type('object', \stdClass::class), 'object', \stdClass::class],
			'true Type null' => [true, new Type('null'), new Type('null')],
			'true Type int' => [true, new Type('int'), new Type('int')],
			'true Type object ' => [true, new Type('object'), new Type('object')],
			'true Type class' => [true, new Type('object', \stdClass::class), new Type('object', \stdClass::class)],
			'true Type class prefix' => [true, new Type('object', '\stdClass'), new Type('object', 'stdClass')],
			'false unknown' => [false, new Type('qdadqw'), 'qdadqw'],
			'false null' => [false, new Type('null'), 'int'],
			'false object' => [false, new Type('object'), 'string'],
			'false string' => [false, new Type('string'), 'object'],
			'false Type null' => [false, new Type('null'), new Type('int')],
			'false Type object' => [false, new Type('object'), new Type('string')],
			'false Type class' => [false, new Type('object', \stdClass::class), new Type('object', \ArrayIterator::class)],
		];
	}

	public function dataValues()
	{
		return [
			'null' => [null],
			'bool' => [true, false],
			'int' => [0, 42, -8],
			'float' => [0.0, 3.14, 4.2],
			'string' => ['foo', '1234'],
			'array' => [[], [1, 2, 3]],
			'object' => [(object)[], function () {}, new \ArrayObject()],
		];
	}

	public function casesCheck()
	{
		return [
			'null' => [new Type('null'), ['null']],
			'bool' => [new Type('bool'), ['bool']],
			'int' => [new Type('int'), ['int']],
			'float' => [new Type('float'), ['float']],
			'string' => [new Type('string'), ['string']],
			'array' => [new Type('array'), ['array']],
			'object' => [new Type('object'), ['object']],
		];
	}

	public function casesConstructFromVar()
	{
		return [
			['null', 'null'],
			['bool', 'bool'],
			['int', 'int'],
			['float', 'float'],
			['string', 'string'],
			['array', 'array'],
			['object', 'object', ['stdClass', 'Closure', 'ArrayObject']],
		];
	}
}
