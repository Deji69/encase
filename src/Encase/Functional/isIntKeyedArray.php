<?php
namespace Encase\Functional;

/**
 * Check whether the given value is an array containing integer keys.
 *
 * @param  mixed $value
 * @return bool  True if `$value` is a integer keyed array, otherwise false.
 */
function isIntKeyedArray($value)
{
	if (empty($value) || !\is_array($value)) {
		return false;
	}

	foreach (\array_keys($value) as $key) {
		if (\is_int($key)) {
			return true;
		}
	}

	return false;
}
