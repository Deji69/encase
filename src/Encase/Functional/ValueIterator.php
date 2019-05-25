<?php
namespace Encase\Functional;

class ValueIterator implements \Iterator
{
	/** @var iterable|array */
	private $iterable;

	public function __construct(iterable $iterable)
	{
		$this->iterable = $iterable;
	}

	public function current(): Value
	{
		$value = \current($this->iterable);
		return $value instanceof Value ? $value : new Value($value);
	}

	public function key()
	{
		return \key($this->iterable);
	}

	public function next()
	{
		\next($this->iterable);
	}

	public function rewind()
	{
		\reset($this->iterable);
	}

	public function valid(): bool
	{
		return \key($this->iterable) !== null;
	}
}
