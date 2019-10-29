<?php
namespace Encase\Regex\Tests;

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

	public function testHasModifier()
	{
		$regex = Regex::make('/test/Am');
		$this->assertTrue($regex->hasModifier('A'));
		$this->assertTrue($regex->hasModifier('m'));
		$this->assertFalse($regex->hasModifier('i'));
		$this->assertFalse($regex->hasModifier('u'));
	}

	public function testAddModifier()
	{
		$regex = Regex::make('/test/');
		$regex = $regex->addModifier('A');
		$regex = $regex->addModifier('m');
		$regex = $regex->addModifier('i');
		$this->assertSame('Ami', $regex->getModifiers());
	}
}
