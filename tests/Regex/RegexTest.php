<?php
namespace Tests\Functional;

use Encase\Tests\TestCase;
use Encase\Regex\Regex;

class RegexTest extends TestCase
{
	public function testConstruction()
	{
		$regex = Regex::make('/test/');
		$this->assertSame('/test/', $regex->getPattern());
		$newRegex = Regex::make($regex);
		$this->assertSame('/test/', $newRegex->getPattern());
	}

	public function testSplit()
	{
		$split = Regex::split('string-to-split', '/\-/');
		$this->assertSame(['string', 'to', 'split'], $split);
	}
}
