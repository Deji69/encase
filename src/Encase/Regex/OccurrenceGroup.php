<?php
namespace Encase\Regex;

use Countable;
use ArrayAccess;
use LogicException;
use InvalidArgumentException;

class OccurrenceGroup implements ArrayAccess, Countable
{
	/** @var Occurrence[] */
	protected $occurrencees = [];

	/**
	 * Construct a sub-occurrence group.
	 *
	 * @param Occurrence[] $occurrences
	 */
	public function __construct(array $occurrences = [])
	{
		foreach ($occurrences as $occurrence) {
			if (!$occurrence instanceof Occurrence) {
				throw new InvalidArgumentException(
					'Array must contain only Occurrence instances'
				);
			}
		}

		$this->occurrences = $occurrences;
	}

	/**
	 * Get a sub-occurrence.
	 *
	 * @param  int $index
	 * @return Occurrence
	 */
	public function getOccurrence(int $index): Occurrence
	{
		return $this->occurrences[$index];
	}

	/**
	 * Count the number of sub-occurrences.
	 *
	 * @return int
	 */
	public function count(): int
	{
		return \count($this->occurrences);
	}

	/**
	 * Check if a sub-occurrence exists at `$index`.
	 *
	 * @param  int $offset
	 * @return bool
	 */
	public function offsetExists($index): bool
	{
		return isset($this->occurrences[$index]);
	}

	/**
	 * @param  int $offset
	 * @return Occurrence
	 */
	public function offsetGet($offset)
	{
		return $this->getOccurrence($offset);
	}

	public function offsetSet($offset, $value): void
	{
		throw new LogicException('Attempting to write to immutable '.\get_class().' object');
	}

	public function offsetUnset($offset): void
	{
		throw new LogicException('Attempting to write to immutable '.\get_class().' object');
	}

	public static function fromResults(array $results)
	{
		$occurrences = [];
		$result = \array_shift($results);
		$end = \strlen($result[0]) + (int)$result[1];

		while ($subResult = \reset($results)) {
			$string = $subResult[0];
			$offset = (int)$subResult[1];

			if ($offset >= $end) {
				break;
			}

			$subOccurrenceGroup = static::fromResults($results);
			$occurrences[] = new Occurrence($string, $offset, $subOccurrenceGroup);
			$results = \array_slice($results, $subOccurrenceGroup->count() + 1);
		}

		return new static($occurrences);
	}
}
