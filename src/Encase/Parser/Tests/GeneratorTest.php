<?php
namespace Encase\Parser\Tests;

use Encase\Parser\Lexer;
use PHPUnit\Framework\TestCase;
use Encase\Parser\Notations\EBNF;

class GeneratorTest extends TestCase
{
	public function testGenerateTerminalParserABNF()
	{
		$this->markTestSkipped();
		//$lexer = new Lexer;
		//$lexer->addRule('');
		//$parser = $abnf->generateParser();
		//$result = $parser->parse('abc');
		//$this->assertSame('abc', $result->rule);
	}
}
