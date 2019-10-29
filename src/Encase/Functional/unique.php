<?php
namespace Encase\Functional;

/**
 * Get the unique elements of an array or object.
 * Will return an array containing the keys and values of the first occurrence
 * of each unique value in the array.
 *
 * @param  array|\Traversable|iterable|\stdClass $iterable
 * @param  bool $assoc If TRUE, preserves duplicate values having string keys.
 * @return array
 */
function unique($iterable, bool $keepKeyed = false, int $sortFlags = \SORT_REGULAR)
{
	$type = assertType($iterable, [
		'array', 'string', 'iterable', '\Traversable', 'stdClass'
	], 'iterable');

	if ($keepKeyed) {
		$indexed = [];
		$keyed = [];

		foreach ($iterable as $k => $v) {
			if (\is_int($k)) {
				$indexed[] = $v;
			} else {
				$keyed[$k] = $v;
			}
		}

		return \array_values(\array_unique($indexed)) + $keyed;
	}

	$iterable = toArray($iterable);
	$iterable = \array_unique($iterable, $sortFlags);

	if ($type === 'string') {
		$iterable = join($iterable, '');
	}

	return $iterable;
}
