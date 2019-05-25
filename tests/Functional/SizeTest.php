<?php
namespace Tests\Functional;

use Encase\Tests\TestCase;
use function Encase\Functional\size;
use function Encase\Functional\count;

class SizeTest extends TestCase
{
	/**
	 * @dataProvider casesBasic
	 */
	public function testBasic($value, $expect)
	{
		$this->assertSame(size($value), $expect);
		$this->assertSame(count($value), $expect);
	}

	public function casesBasic()
	{
		$obj = (object)[];
		return [
			'With null' => [null, 0],
			'With zero number' => [0.0, 0],
			'With number' => [3.14, 0],
			'With empty string' => ['', 0],
			'With string' => ['hello', 5],
			'With unicode string' => ['test✔', 5],
			'With DateTime' => [new \DateTime, 0],
			'With empty array' => [[], 0],
			'With array' => [[1, 2, 3], 3],
			'With empty object' => [(object)[], 0],
			'With object' => [(object)['a' => 1, 'b' => 2, 'c' => 3], 3],
			'With empty ArrayObject' => [new \ArrayObject([]), 0],
			'With ArrayObject' => [new \ArrayObject([1, 2, 3]), 3],
		];
	}
}
