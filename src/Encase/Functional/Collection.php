<?php
namespace Encase\Functional;

use Countable;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use CachingIterator;
use JsonSerializable;
use IteratorAggregate;
use function Encase\Functional\split;
use Encase\Functional\Traits\Functional;

class Collection extends Value
{
	protected static $boxedType = [];

	/** @var array */
	protected $items = [];

	/**
	 * Construct a collection.
	 *
	 * @param  mixed  ...$subject
	 */
	public function __construct(...$subject)
	{
		$subject = \func_num_args() === 1 ? $subject[0] : \func_get_args();
		$this->value = self::getArrayableItems($subject);
	}

	/**
	 * Get all items in the collection.
	 *
	 * @return array
	 */
	public function all(): array
	{
		return $this->value;
	}

	/**
	 * Count the number of items in the collection.
	 *
	 * @return int
	 */
	public function count(): int
	{
		return \count($this->value);
	}

	/**
	 * Get the item at the given key.
	 *
	 * @param  int|string  $key
	 * @param  mixed|\Closure|\Encase\Functional\Func  $default
	 * @return mixed
	 */
	public function get($key = null, $default = null)
	{
		if (\func_num_args() > 0) {
			if (!isset($this->value[$key])) {
				if (isType($default, 'function')) {
					return $default();
				}
				return $default;
			}
			return $this->value[$key];
		}
		return $this->value;
	}

	/**
	 * Get a CachingIterator instance.
	 *
	 * @param  int  $flags
	 * @return \CachingIterator
	 */
	public function getCachingIterator($flags = CachingIterator::CALL_TOSTRING)
	{
		return new CachingIterator($this->getIterator(), $flags);
	}

	/**
	 * Get an iterator for the items.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->value);
	}

	/**
	 * Check if the collection is empty.
	 *
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return empty($this->value);
	}

	/**
	 * Serialise the items as an array.
	 *
	 * @return static
	 */
	public function jsonSerialize()
	{
		return \array_map(function ($value) {
			if ($value instanceof JsonSerializable) {
				return $value->jsonSerialize();
			}
			return $value;
		}, $this->value);
	}

	/**
	 * Push an item to the end of the collection.
	 *
	 * @param  mixed  $item
	 * @return $this
	 */
	public function push($item)
	{
		$this->value[] = $item;
		return $this;
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
		$result = $this->callFunctionalMethod($this->value, $method, $params);

		// If the function returns an unmutated copy of its input, we'll return
		// this instance to allow chaining.
		if ($this->isMethodTapped($method)) {
			return $this;
		}

		// If the function returns a mutated copy of its input, we'll return it
		// wrapped in a new Value instance to allow chaining.
		if ($this->isMethodAMutator($method) && !($result instanceof self)) {
			return new self($result);
		}

		// For totally new values being returned, return it without wrapping.
		return $result;
	}

	/**
	 * Create a new collection instance.
	 *
	 * @param  mixed  ...$subject
	 * @return static
	 */
	public static function make(...$subject)
	{
		return new static(...$subject);
	}

	/**
	 * Box value into a collection instance.
	 *
	 * @param  mixed  $value
	 * @return \Encase\Functional\Collection
	 * @throws \Encase\Functional\Exceptions\InvalidTypeError
	 */
	public static function box($value)
	{
		return parent::box($value);
	}

	/**
	 * Results array of items from Collection or Arrayable.
	 *
	 * @param  mixed  $items
	 * @return array
	 */
	protected static function getArrayableItems($items)
	{
		if (\is_array($items)) {
			return $items;
		} elseif (\is_string($items)) {
			return split($items);
		} elseif (\is_integer($items)) {
			return \array_fill(0, $items, null);
		} elseif ($items instanceof self) {
			return $items->all();
		} elseif ($items instanceof \JsonSerializable) {
			return $items->jsonSerialize();
		} elseif ($items instanceof Traversable) {
			return \iterator_to_array($items);
		} elseif (empty($items)) {
			return [];
		}

		return (array)$items;
	}

	/**
	 * Determine if an item exists at an offset.
	 *
	 * @param  mixed  $key
	 * @return bool
	 */
	public function offsetExists($key): bool
	{
		return \array_key_exists($key, $this->value);
	}

	/**
	 * Get an item at a given offset wrapped as a Value.
	 *
	 * @param  mixed  $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->value[$key];
	}

	/**
	 * Set the item at a given offset.
	 *
	 * @param  mixed  $key
	 * @param  mixed  $value
	 */
	public function offsetSet($key, $value): void
	{
		if (\is_null($key)) {
			$this->value[] = $value;
		} else {
			$this->value[$key] = $value;
		}
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @param  string  $key
	 */
	public function offsetUnset($key): void
	{
		unset($this->value[$key]);
	}
}
