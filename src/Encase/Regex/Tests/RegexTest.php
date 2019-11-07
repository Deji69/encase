<?php
namespace Encase\Regex\Tests;

use Encase\Regex\Regex;

class RegexTest extends TestCase
{
	public function testConstruction()
	{
		$regex = Regex::new('/test/');
		$this->assertSame('/test/', $regex->getPattern());
		$newRegex = Regex::new($regex);
		$this->assertSame('/test/', $newRegex->getPattern());
	}

	public function testSplit()
	{
		$split = Regex::split('string-to-split', '/\-/');
		$this->assertSame(['string', 'to', 'split'], $split);
	}

	public function testHasModifier()
	{
		$regex = Regex::new('/test/Am');
		$this->assertTrue($regex->hasModifier('A'));
		$this->assertTrue($regex->hasModifier('m'));
		$this->assertFalse($regex->hasModifier('i'));
		$this->assertFalse($regex->hasModifier('u'));
	}

	public function testAddModifier()
	{
		$regex = Regex::new('/test/');
		$regex = $regex->addModifier('A');
		$regex = $regex->addModifier('m');
		$regex = $regex->addModifier('i');
		$this->assertSame('Ami', $regex->getModifiers());
	}

	public function testIsRegexString()
	{
		$this->assertTrue(Regex::isRegexString('/test/'));
		$this->assertTrue(Regex::isRegexString('/([A-Z\w]+).*/i'));
		$this->assertFalse(Regex::isRegexString('/\/'));
		$this->assertFalse(Regex::isRegexString('/\\/'));
		$this->assertTrue(Regex::isRegexString('/\\\/'));
		$this->assertFalse(Regex::isRegexString('/\\\\\/'));
		$this->assertFalse(Regex::isRegexString('/\\\\\\/'));
		$this->assertTrue(Regex::isRegexString('/\\\\\\\/'));
	}
}
