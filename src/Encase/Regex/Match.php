<?php
namespace Encase\Regex;

use LogicException;

use Encase\Regex\MatchGroup;
use function Encase\Functional\slice;

class Match implements \ArrayAccess, \Countable
{
	/** @var int */
	protected $offset;

	/** @var string */
	protected $string;

	/** @var MatchGroup */
	protected $group;

	public function __construct(string $match, int $offset)
	{
		$this->group = new MatchGroup;
		$this->string = $match;
		$this->offset = $offset;
	}

	public function getString(): string
	{
		return $this->string;
	}

	public function getMatch(int $index): Match
	{
		return $this->group[$index];
	}

	public function count()
	{
		return \count($this->group);
	}

	public function offsetExists($offset)
	{
		return isset($this->group[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->group[$offset];
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
		if (empty($matches)) {
			return null;
		}

		$match = new Match(...$matches[0]);
		$match->group = MatchGroup::fromResults(slice($matches, 1));
	}
}
