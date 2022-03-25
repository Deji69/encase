<?php
namespace Encase\Regex;

use Countable;
use ArrayAccess;
use LogicException;
use Encase\Regex\OccurrenceGroup;

class Occurrence implements ArrayAccess, Countable
{
	/** @var int */
	protected $offset;

	/** @var string */
	protected $string;

	/** @var OccurrenceGroup */
	protected $group;

	public function __construct(string $occurrence, int $offset, OccurrenceGroup $subOccurrenceGroup = null)
	{
		$this->string = $occurrence;
		$this->offset = $offset;
		$this->group = $subOccurrenceGroup ?? new OccurrenceGroup();
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
	 * Get a sub-occurrence from the group.
	 *
	 * @param  int $index
	 * @return Occurrence
	 */
	public function getOccurrence(int $index): Occurrence
	{
		return $this->group[$index];
	}

	/**
	 * Get the number of sub-occurrences in the group.
	 *
	 * @return int
	 */
	public function count(): int
	{
		return \count($this->group);
	}

	/**
	 * Check whether a sub-occurrence exists at the group index.
	 *
	 * @param  int $index
	 * @return bool TRUE if there is a sub-occurrence at `$index`.
	 */
	public function offsetExists($index): bool
	{
		return isset($this->group[$index]);
	}

	/**
	 * Get the sub-occurrence at `$index`.
	 *
	 * @param  int $index
	 * @return Occurrence
	 */
	public function offsetGet($index)
	{
		return $this->group[$index];
	}

	public function offsetSet($offset, $value): void
	{
		throw new LogicException('Attempting to write to immutable '.\get_class().' object');
	}

	public function offsetUnset($offset): void
	{
		throw new LogicException('Attempting to write to immutable '.\get_class().' object');
	}

	/**
	 * Create a Occurrence object from the given `preg_match` occurrences array.
	 *
	 * @param  array $occurrences
	 * @return Occurrence|null Returns NULL if `$occurrence` is empty.
	 */
	public static function fromResults(array $occurrences)
	{
		if (empty($occurrences)) {
			return null;
		}

		$string = $occurrences[0][0];
		$offset = $occurrences[0][1];
		return new self($string, $offset, OccurrenceGroup::fromResults($occurrences));
	}
}
