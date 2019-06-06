<?php
namespace Encase\Functional;

use Encase\Functional\Traits\Functional;

class BoxIterator implements \Iterator
{
	use Functional;

	/** @var iterable|array */
	private $iterable;

	public function __construct(iterable $iterable)
	{
		$this->iterable = $iterable;
	}

	/**
	 * Handles chainable method calls to Functional functions.
	 * Returns $this if the method does not mutate or returns a new value.
	 * Otherwise, a new Value instance is returned containing the value.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return static|$this
	 */
	public function __call($method, $params = [])
	{
		// Call the Functional function.
		$result = $this->callFunctionalMethod($this, $method, $params);

		// If the function returns an unmutated copy of its input, we'll return
		// this instance to allow chaining.
		if ($this->isMethodTapped($method)) {
			return $this;
		}

		// If the function returns a mutated copy of its input, we'll return it
		// wrapped in a new Value instance to allow chaining.
		if ($this->isMethodAMutator($method) && !($result instanceof static)) {
			return Value::box($result);
		}

		// For totally new values being returned, return it without wrapping.
		return $result;
	}

	public function current(): Value
	{
		$value = \current($this->iterable);
		return $value instanceof Value ? $value : Value::box($value);
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
