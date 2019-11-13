<?php
namespace Encase\Regex;

use Countable;
use ArrayAccess;
use LogicException;
use Encase\Regex\MatchGroup;

class Match implements ArrayAccess, Countable
{
	/** @var int */
	protected $offset;

	/** @var string */
	protected $string;

	/** @var MatchGroup */
	protected $group;

	public function __construct(string $match, int $offset, MatchGroup $subMatchGroup = null)
	{
		$this->string = $match;
		$this->offset = $offset;
		$this->group = $subMatchGroup ?? new MatchGroup();
	}

	/**
	 * Get the matching string.
	 *
	 * @return string
	 */
	public function getString(): string
	{
		return $this->string;
	}

	/**
	 * Get the match offset.
	 *
	 * @return int
	 */
	public function getOffset(): int
	{
		return $this->offset;
	}

	/**
	 * Get a sub-match from the group.
	 *
	 * @param  int $index
	 * @return Match
	 */
	public function getMatch(int $index): Match
	{
		return $this->group[$index];
	}

	/**
	 * Get the number of sub-matches in the group.
	 *
	 * @return int
	 */
	public function count()
	{
		return \count($this->group);
	}

	/**
	 * Check whether a sub-match exists at the group index.
	 *
	 * @param  int $index
	 * @return bool TRUE if there is a sub-match at `$index`.
	 */
	public function offsetExists($index)
	{
		return isset($this->group[$index]);
	}

	/**
	 * Get the sub-match at `$index`.
	 *
	 * @param  int $index
	 * @return Match
	 */
	public function offsetGet($index)
	{
		return $this->group[$index];
	}

	public function offsetSet($offset, $value)
	{
		throw new LogicException('Attempting to write to immutable '.\get_class().' object');
	}

	public function offsetUnset($offset)
	{
		throw new LogicException('Attempting to write to immutable '.\get_class().' object');
	}

	/**
	 * Create a Match object from the given `preg_match` matches array.
	 *
	 * @param  array $matches
	 * @return Match|null Returns NULL if `$matches` is empty.
	 */
	public static function fromResults(array $matches)
	{
		if (empty($matches)) {
			return null;
		}

		$string = $matches[0][0];
		$offset = $matches[0][1];
		return new self($string, $offset, MatchGroup::fromResults($matches));
	}
}
