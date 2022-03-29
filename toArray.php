<?php
namespace Encase\Functional;

/**
 * Converts array-like objects into arrays.
 * Any objects stored within the elements are cloned to preserve immutability.
 *
 * @param  string|\Traversable|\stdClass $value The object to convert.
 * @return array Empty array if `$value` is not iterable.
 */
function toArray($value)
{
	if (\is_string($value)) {
		return split($value);
	}
	if (isType($value, ['Traversable', 'stdClass'])) {
		$array = [];

		foreach ($value as $key => $val) {
			$array[$key] = \is_object($val) ? clone $val : $val;
		}

		return $array;
	}
	return (array)$value;
}
