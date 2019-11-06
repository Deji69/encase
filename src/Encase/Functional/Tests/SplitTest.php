<?php
namespace Encase\Functional\Tests;

use Encase\Regex\Regex;
use Encase\Functional\Str;
use Encase\Functional\Collection;
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

	public function testSplitWrappedStr()
	{
		$string = Str::make('foo');
		$result = $string->split();
		$this->assertEquals(Collection::make('f', 'o', 'o'), $result);
	}

	public function testSplitByRegex()
	{
		$string = 'hel.lo|wor/ld';
		$result = split($string, Regex::new('/[^\w]/'));
		$this->assertSame(['hel', 'lo', 'wor', 'ld'], $result);
	}
}
