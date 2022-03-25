<?php
namespace Encase\Parser\Tests;

use Encase\Parser\Source;
use PHPUnit\Framework\TestCase;

class SourceTest extends TestCase
{
	const MULTILINE_STRING = <<<'EOD'
Line One
§ Liné 2

Łine 4
EOD;

	public function testGetCode()
	{
		$source = $this->getSource();
		$this->assertSame('hello world', $source->getCode());
	}

	public function testGetName()
	{
		$source = $this->getSource('hi', 'test');
		$this->assertSame('test', $source->getName());
	}

	public function testGetPath()
	{
		$source = $this->getSource('hi', 'test', '/my/path');
		$this->assertSame('/my/path', $source->getPath());
	}

	public function testGetSize()
	{
		$source = $this->getSource('hello');
		$this->assertSame(5, $source->getSize());
		$source = $this->getSource('héłló');
		$this->assertSame(5, $source->getSize());
	}

	public function testGetToken()
	{
		$source = $this->getSource('hello world');
		$this->assertSame('hello ', $source->getToken(0, 6));
		$source = $this->getSource('hello world');
		$this->assertSame('world', $source->getToken(6, 5));
	}

	public function testGetTokenDelimited()
	{
		$source = $this->getSource('hello world');
		$this->assertSame('hello', $source->getToken(0));
		$this->assertSame('llo', $source->getToken(2));
		$this->assertSame('world', $source->getToken(6));
	}

	public function testGetTokenReturnsNullOnInvalidRange()
	{
		$source = $this->getSource('hello world');
		$this->assertNull($source->getToken(-1));
		$this->assertNull($source->getToken(4, -1));
		$this->assertNull($source->getToken(4, 100));
		$this->assertNull($source->getToken(100));
	}

	public function testGetLineOffset()
	{
		$source = $this->getSource(self::MULTILINE_STRING);
		$this->assertSame(0, $source->getLineOffset(1));
		$this->assertSame(9, $source->getLineOffset(2));
		$this->assertSame(20, $source->getLineOffset(3));
		$this->assertSame(21, $source->getLineOffset(4));
	}

	public function testGetOffsetLine()
	{
		$source = $this->getSource(self::MULTILINE_STRING);
		$this->assertSame(1, $source->getOffsetLine(0));
		$this->assertSame(1, $source->getOffsetLine(8));
		$this->assertSame(2, $source->getOffsetLine(9));
		$this->assertSame(2, $source->getOffsetLine(19));
		$this->assertSame(3, $source->getOffsetLine(20));
		$this->assertSame(4, $source->getOffsetLine(21));
		$this->assertSame(4, $source->getOffsetLine(50));
	}

	public function testGetOffsetColumn()
	{
		$source = $this->getSource(self::MULTILINE_STRING);
		$this->assertSame(1, $source->getOffsetColumn(0));
		$this->assertSame(2, $source->getOffsetColumn(1));
		$this->assertSame(1, $source->getOffsetColumn(9));
		$this->assertSame(9, $source->getOffsetColumn(19));
		$this->assertSame(1, $source->getOffsetColumn(20));
		$this->assertSame(1, $source->getOffsetColumn(21));
		$this->assertSame(7, $source->getOffsetColumn(27));
	}

	public function getSource($str = 'hello world', $name = 'test', $path = null): Source
	{
		return new Source($str, $name, $path);
	}
}
