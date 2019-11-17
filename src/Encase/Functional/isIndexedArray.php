<?php
namespace Encase\Functional;

/**
 * Check whether the given value is an indexed array.
 * An indexed array is qualified as an array with only integer keys in the
 * range of [0, `count($value)`), in no particular order.
 *
 * @param  mixed $value
 * @return bool  True if `$value` is an indexed array, otherwise false.
 */
function isIndexedArray($value)
{
	if (!\is_array($value)) {
		return false;
	}

	$max = \count($value);

	foreach (\array_keys($value) as $key) {
		if (!\is_int($key) || $key < 0 || $key >= $max) {
			return false;
		}
	}

	return true;
}
