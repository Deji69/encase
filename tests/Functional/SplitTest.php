<?php
namespace Tests\Functional;

use Encase\Tests\TestCase;
use function Encase\Functional\split;

class SplitTest extends TestCase
{
	public function testSplit()
	{
		$string = 'foo.bar.cat';
		$result = split($string, '.');
		$this->assertSame(['foo', 'bar', 'cat'], $result);
	}

	public function testSplitIntoCharacters()
	{
		$string = 'hello';
		$result = split($string);
		$this->assertSame(['h', 'e', 'l', 'l', 'o'], $result);
	}

	public function testSplitLimit()
	{
		$string = 'foobar';
		$result = split($string, '', 4);
		$this->assertSame(['f', 'o', 'o', 'bar'], $result);
	}

	public function testSplitUnicode()
	{
		$string = 'foo✔bar✔cat';
		$result = split($string, '✔');
		$this->assertSame(['foo', 'bar', 'cat'], $result);
		$string = '✔✔✔';
		$result = split($string);
		$this->assertSame(['✔', '✔', '✔'], $result);
	}
}
