<?php
namespace Encase\Functional\Tests;

use function Encase\Functional\take;
use function Encase\Functional\isType;

class TakeTest extends TestCase
{
	/**
	 * @dataProvider casesIterables
	 */
	public function testTake($data, $expect)
	{
		foreach ($expect as $checkType => $expectArrays) {
			foreach ($expectArrays as $count => $expectArray) {
				if (isType($data, 'function')) {
					$values = $data();
				} else {
					$values = $data;
				}

				$result = take($values, $count);
				$test = [
					'same' => [self::class, 'assertSame'],
					'equals' => [self::class, 'assertEquals'],
					'notSame' => [self::class, 'assertNotSame'],
					'notEquals' => [self::class, 'assertNotEquals'],
				][$checkType];
				$test($expectArray, $result);
			}
		}
	}

	public function casesIterables()
	{
		yield 'With string' => [
			'data' => 'foobar',
			'expect' => [
				'same' => [
					1 => 'f',
					3 => 'foo',
					7 => 'foobar'
				]
			]
		];

		yield 'With array' => [
			'data' => [1, 2, 3, 4],
			'expect' => [
				'same' => [
					1 => [1],
					2 => [1, 2],
					3 => [1, 2, 3],
					4 => [1, 2, 3, 4],
					6 => [1, 2, 3, 4]
				]
			]
		];

		yield 'With object' => [
			'data' => (object)['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
			'expect' => [
				'same' => [
					1 => ['a' => 1],
					3 => ['a' => 1, 'b' => 2, 'c' => 3],
					4 => ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
					6 => ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]
				]
			]
		];

		yield 'With \ArrayObject' => [
			'data' => new \ArrayObject([1, 2, 3, 4]),
			'expect' => [
				'same' => [
					1 => [1],
					3 => [1, 2, 3],
					4 => [1, 2, 3, 4],
					6 => [1, 2, 3, 4]
				]
			]
		];

		yield 'With \Generator' => [
			'data' => function () {
				yield 'a' => 1;
				yield 'b' => 2;
				yield 'c' => 3;
				yield 'd' => 4;
			},
			'expect' => [
				'same' => [
					1 => ['a' => 1],
					3 => ['a' => 1, 'b' => 2, 'c' => 3],
					6 => ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]
				]
			]
		];
	}
}
