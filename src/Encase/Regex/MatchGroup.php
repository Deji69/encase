<?php
namespace Encase\Regex;

use LogicException;

class MatchGroup
{
	/** @var Match[] */
	protected $matches = [];

	public function __construct(array $matches = [])
	{
		foreach ($matches as $match) {
			$this->matches[] = new Match($match[0], $match[1]);
		}
		for ($i = 0; $i < \count($matches); ++$i) {
			if ($i === 0) {
				parent::__construct($matches[0]);
				$end = $this->offset + \strlen($this->string);
			} else {
				$subMatches = [$matches[$i]];

				for (; \count($matches) > ($i) && $matches[$i + 1][1] < $end; ++$i) {
					$subMatches[] = $matches[$i + 1];
				}

				if (\count($subMatches) > 1) {
					$this->groups[] = new MatchGroup($subMatches);
				} else {
					$this->groups[] = new Match($subMatches[0]);
				}
			}
		}
	}

	public function getMatch(int $index): Match
	{
		return $this->matches[$index];
	}

	public function count()
	{
		return \count($this->matches);
	}

	public function offsetExists($offset)
	{
		return isset($this->matches[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->matches[$offset];
	}

	public function offsetSet($offset, $value)
	{
		throw new LogicException('Attempting to write to immutable '.\get_class().' object');
	}

	public function offsetUnset($offset)
	{
		throw new LogicException('Attempting to write to immutable '.\get_class().' object');
	}

	public static function fromResults(array $matches)
	{
		foreach ($matches as $match) {

		}
	}
}
