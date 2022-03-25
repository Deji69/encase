<?php
namespace Encase\Functional\Tests;

use function Encase\Functional\flat;

class FlatTest extends TestCase
{
	/** @dataProvider casesArray */
	public function testFlattenArray($input, $expect, $depth = null)
	{
		$result = flat($input, $depth);
		$this->assertSame($expect, $result);
	}

	public function casesArray()
	{
		return [
			[[1, 2, 3], [1, 2, 3]],
			[[[1, 2], [3, 4], [5], 6], [1, 2, 3, 4, 5, 6]],
			[
				[[1, [2]], [[3], 4], [[[5]]], 6],
				[1, 2, 3, 4, 5, 6]
			],
			[[1, [2], 3, [[4, 5], 6]], [1, 2, 3, [4, 5], 6], 1],
			[[1, [[2]], [3], [[[4]]]], [1, 2, 3, [4]], 2],
		];
	}
}
