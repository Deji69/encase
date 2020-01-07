<?php
namespace Encase\Functional\Tests;

use Mockery as m;
use function Encase\Functional\each;
use Encase\Functional\Exceptions\InvalidTypeError;

class EachTest extends TestCase
{
	/** @var \Mockery\MockInterface */
	protected $mock;

	protected function setUp(): void
	{
		parent::setUp();
		$this->mock = m::mock();
	}

	public function casesEmptyNonIterables()
	{
		return [
			[null],
			[false],
			[0],
		];
	}

	public function casesNonEmptyNonIterables()
	{
		return [
			[1],
			[true],
		];
	}

	/**
	 * @dataProvider casesBasic
	 */
	public function testBasic($collection)
	{
		$mock = $this->mockCall($collection);
		$result = each($collection, [$mock, 'call']);
		$this->assertNull($result);
	}

	public function testClosure()
	{
		$values = ['a' => 1, 'b' => 2, 'c' => 3];
		$output = [];

		$fn = function ($value, $key, $collection) use (&$output, $values) {
			$output[$key] = $value;
			$this->assertSame($values, $collection);
		};

		$result = each($values, $fn);
		$this->assertSame($values, $output);
		$this->assertNull($result);
	}

	public function testEarlyExit()
	{
		$input = ['a', 'b', 'c', 'd'];
		$output = [];

		$result = each($input, function ($value) use (&$output) {
			$output[] = $value;

			if ($value == 'b') {
				return false;
			}
		});

		$this->assertFalse($result);
		$this->assertSame(['a', 'b'], $output);
	}

	public function testEarlyExitWithReturn()
	{
		$result = each([1, 2, 3], function ($value) {
			if ($value === 2) {
				return $value;
			}
		});
		$this->assertSame(2, $result);
	}

	/** @dataProvider casesEmptyNonIterables */
	public function testDoesNothingWithEmptyNonIterables($value)
	{
		$called = false;
		each($value, function () use (&$called) {
			$called = true;
		});
		$this->assertFalse($called);
	}

	/** @dataProvider casesNonEmptyNonIterables */
	public function testErrorsWithNonEmptyNonIterables($value)
	{
		$type = \is_object($value) ? \get_class($value) : \gettype($value);

		$this->expectException(InvalidTypeError::class);
		$this->expectExceptionMessage(
			"Argument 0 (\$iterable) of Encase\\Functional\\each expects "
			."iterable, stdClass or string, $type given"
		);

		$called = false;

		each($value, function () use (&$called) {
			$called = true;
		});

		$this->assertFalse($called);
	}

	public function testWithString()
	{
		$output = [];
		$input = 'hello';
		$expect = [
			['h', 0, 'hello'],
			['e', 1, 'hello'],
			['l', 2, 'hello'],
			['l', 3, 'hello'],
			['o', 4, 'hello'],
		];

		each($input, function ($value, $key, $string) use (&$output) {
			$output[] = [$value, $key, $string];
		});

		$this->assertSame($expect, $output);
	}

	public function testWithUnicodeString()
	{
		$output = '';
		$input = 'áëìȯũ';
		each($input, function ($value) use (&$output) {
			$output = $value.$output;
		});
		$this->assertSame('ũȯìëá', $output);
	}

	/**
	 * @dataProvider casesInvalidArgumentExceptions
	 */
	public function testTypeAssertions($value, $type)
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage(
			"Argument 0 (\$iterable) of Encase\\Functional\\each expects ".
			"iterable, stdClass or string, $type given"
		);
		each($value, function () {
			//
		});
	}

	public function mockCall($collection)
	{
		$mock = m::mock();

		if (empty($collection)) {
			$mock->shouldNotReceive('call');
		} else {
			foreach ($collection as $key => $value) {
				$mock->expects('call')
				     ->with($value, $key, $collection)
				     ->once();
			}
		}
		return $mock;
	}

	public function casesBasic()
	{
		yield 'With null' => [
			null
		];
		yield 'With empty array' => [
			[]
		];
		yield 'With array' => [
			['first', 'second', 'third']
		];
		yield 'With associative array' => [
			['a' => 'first', 'b' => 'second', 'c' => 'third']
		];
		yield 'With empty object' => [
			(object)[]
		];
		yield 'With object' => [
			(object)['a' => 'first', 'b' => 'second', 'c' => 'third']
		];
		yield 'With empty ArrayObject' => [
			new \ArrayObject([])
		];
		yield 'With ArrayObject' => [
			new \ArrayObject(['a' => 'first', 'b' => 'second', 'c' => 'third'])
		];
		yield 'With Iterator' => [
			(new \ArrayObject(['a' => 'first', 'b' => 'second', 'c' => 'third']))->getIterator()
		];
	}

	public function casesInvalidArgumentExceptions()
	{
		yield 'With zero number' => [
			'iterable' => 0,
			'type' => 'integer',
		];
		yield 'With number' => [
			'iterable' => 3.14,
			'type' => 'double',
		];
		yield 'With DateTime' => [
			'iterable' => new \DateTime(),
			'type' => 'DateTime',
		];
	}
}
