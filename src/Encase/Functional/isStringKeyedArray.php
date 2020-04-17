<?php
namespace Encase\Functional;

/**
 * Check whether the given value is an array containing string keys.
 *
 * @param  mixed $value
 * @return bool  True if `$value` is a string keyed array, otherwise false.
 */
function isStringKeyedArray($value)
{
	if (empty($value) || !\is_array($value)) {
		return false;
	}

	foreach (\array_keys($value) as $key) {
		if (\is_string($key)) {
			return true;
		}
	}

	return false;
}
