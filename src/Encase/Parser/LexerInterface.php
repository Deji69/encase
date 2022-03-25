<?php
namespace Encase\Parser;

interface LexerInterface
{
	public function tokenize(Source $source): TokenStream;
}
