<?php
namespace Encase\Functional\Tests;

use Encase\Functional\Str;

use Encase\Functional\Func;
use function Encase\Functional\fill;

class FillTest extends TestCase
{
	public function testFillArrayWithString()
	{
		$result = fill([], ':)', 4);
		$this->assertSame([':)', ':)', ':)', ':)'], $result);
	}

	public function testFillStringWithString()
	{
		$result = fill('', ':)', 5);
		$this->assertSame(':):):', $result);
	}

	public function testFillArrayWithGenerator()
	{
		$result = fill([], Func::box($this->predictableStringGenerator(4)), 5);
		$this->assertSame([
			'aaaa', 'bbbb', 'cccc', 'dddd', 'eeee'
		], $result);
	}

	public function testFillStringWithGenerator()
	{
		$result = fill('', Func::box($this->predictableStringGenerator(3)), 3);
		$this->assertSame('aaabbbccc', $result);
	}

	public function predictableStringGenerator($length = 1)
	{
		for ($i = 0; true; ++$i) {
			yield \str_repeat(\chr(($i % 26) + \ord('a')), $length);
		}
	}
}
