<?php
namespace Encase\Functional;

use Traversable;
use JsonSerializable;
use function Encase\Functional\split;

class Collection extends Value
{
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
}
