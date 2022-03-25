<?php
namespace Encase\Functional\Tests;

use ArrayObject;

use function Encase\Functional\values;

class ValuesTest extends TestCase
{
	/** @dataProvider casesBasic */
	public function testBasic($input, $expect)
	{
		$result = values($input);

		if (\is_object($result)) {
			$this->assertEquals($expect, $result);
		} else {
			$this->assertSame($expect, $result);
		}
	}

	public function casesBasic()
	{
		return [
			'Null' => [
				'input' => null,
				'expect' => [],
			],
			'Array' => [
				'input' => [1, 2, 3, 4],
				'expect' => [1, 2, 3, 4],
			],
			'Assoc array' => [
				'input' => ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
				'expect' => [1, 2, 3, 4],
			],
			'stdObj' => [
				'input' => (object)['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
				'expect' => [1, 2, 3, 4],
			],
			'ArrayObject' => [
				'input' => new ArrayObject(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]),
				'expect' => new ArrayObject([1, 2, 3, 4]),
			],
		];
	}
}
