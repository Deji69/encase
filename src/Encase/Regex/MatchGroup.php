<?php
namespace Encase\Regex;

use Countable;
use ArrayAccess;
use LogicException;
use InvalidArgumentException;

class MatchGroup implements ArrayAccess, Countable
{
	/** @var Match[] */
	protected $matches = [];

	/**
	 * Construct a sub-match group.
	 *
	 * @param Match[] $matches
	 */
	public function __construct(array $matches = [])
	{
		foreach ($matches as $match) {
			if (!$match instanceof Match) {
				throw new InvalidArgumentException(
					'Array must contain only Match instances'
				);
			}
		}

		$this->matches = $matches;
	}

	/**
	 * Get a sub-match.
	 *
	 * @param  int $index
	 * @return Match
	 */
	public function getMatch(int $index): Match
	{
		return $this->matches[$index];
	}

	/**
	 * Count the number of sub-matches.
	 *
	 * @return int
	 */
	public function count()
	{
		return \count($this->matches);
	}

	/**
	 * Check if a sub-match exists at `$index`.
	 *
	 * @param  int $offset
	 * @return bool
	 */
	public function offsetExists($index)
	{
		return isset($this->matches[$index]);
	}

	/**
	 * @param  int $offset
	 * @return Match
	 */
	public function offsetGet($offset)
	{
		return $this->getMatch($offset);
	}

	public function offsetSet($offset, $value)
	{
		throw new LogicException('Attempting to write to immutable '.\get_class().' object');
	}

	public function offsetUnset($offset)
	{
		throw new LogicException('Attempting to write to immutable '.\get_class().' object');
	}

	public static function fromResults(array $results)
	{
		$matches = [];
		$result = \array_shift($results);
		$end = \strlen($result[0]) + (int)$result[1];

		while ($subResult = \reset($results)) {
			$string = $subResult[0];
			$offset = (int)$subResult[1];

			if ($offset >= $end) {
				break;
			}

			$subMatchGroup = static::fromResults($results);
			$matches[] = new Match($string, $offset, $subMatchGroup);
			$results = \array_slice($results, $subMatchGroup->count() + 1);
		}

		return new static($matches);
	}
}
