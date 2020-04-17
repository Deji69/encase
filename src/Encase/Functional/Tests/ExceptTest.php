<?php
namespace Encase\Functional\Tests;

use function Encase\Functional\split;
use function Encase\Functional\except;

class ExceptTest extends TestCase
{
	/** @dataProvider casesBasic */
	public function testBasic($input, $pred, $expect)
	{
		if (\is_string($input)) {
			$input = split($input);
			$expect = split($expect);
		}

		$result = except($input, $pred);
		$this->assertSame($expect, $result);
	}

	public function casesBasic()
	{
		return [
			[[1, 2, 3], 2, [1, 3]],
			[[1, 2, null, 3, null], null, [1, 2, 3]],
			['The quick brown fox jumped', fn($v) => \in_array($v, split('aeiou')), 'Th qck brwn fx jmpd']
		];
	}
}
