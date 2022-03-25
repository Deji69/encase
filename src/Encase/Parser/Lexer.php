<?php
namespace Encase\Parser;

class Lexer implements LexerInterface
{
	public function tokenize(Source $source): TokenStream
	{
		$offset = 0;
		return new TokenStream();
	}
}
