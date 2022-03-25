<?php
namespace Encase\Parser;

interface GrammarInterface
{
	public function addRule(string $name, string $definition);
	public function addRules(array $definitions);
	public function generateParser(): ParserInterface;
}
