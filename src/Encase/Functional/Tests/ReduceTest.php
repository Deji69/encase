<?php
namespace Encase\Functional\Tests;

use function Encase\Functional\reduce;

class ReduceTest extends TestCase
{
	public function testAdd() {
		$result = reduce([1, 2, 3], function ($current, $value) {
			return $current + $value;
		}, 0);
		$this->assertSame(6, $result);
	}

	public function testMultiply() {
		$result = reduce([1, 2, 3, 4], function ($current, $value) {
			return $current * $value;
		}, 1);
		$this->assertSame(24, $result);
	}

	public function testMultiplyWithDefaultInitial() {
		$result = reduce([1, 2, 3, 4], function ($current, $value) {
			return $current * $value;
		});
		$this->assertSame(24, $result);
	}

	public function testInitialIntDefaultPredicateIsAddition() {
		$result = reduce([2, 4, 6, 8]);
		$this->assertSame(20, $result);
	}

	public function testInitialFloatDefaultPredicateIsAddition() {
		$result = reduce([2.0, 4.5, 6.0, 8.5]);
		$this->assertSame(21.0, $result);
	}

	public function testInitialStringDefaultPredicateIsConcatenation() {
		$result = reduce(['1', '2', '3', '4']);
		$this->assertSame('1234', $result);
	}

	public function testInitialArrayDefaultPredicateIsAppend() {
		$result = reduce([3, 4, 5], null, [1, 2]);
		$this->assertSame([1, 2, 3, 4, 5], $result);
	}

	public function testDefaultPredicateIsReplace() {
		$result = reduce(['a', 'b', 'c', 'd'], null, false);
		$this->assertSame('d', $result);
	}
}
