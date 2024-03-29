<?php
namespace Encase\Regex\Tests;

use Encase\Regex\Occurrence;

class OccurrenceTest extends TestCase
{
	public function testConstructFromResults()
	{
		$match = Occurrence::fromResults([
			['hello', 0],
			['hell', 0],
			['he', 0],
			['ll', 2],
			['o', 4],
		]);
		$this->assertSame('hello', $match->getString());
		$this->assertCount(2, $match);
		$this->assertSame('hell', $match[0]->getString());
		$this->assertCount(2, $match[0]);
		$this->assertSame('he', $match[0][0]->getString());
		$this->assertSame('ll', $match[0][1]->getString());
		$this->assertSame('o', $match[1]->getString());
		$this->assertCount(0, $match[1]);
	}
}
