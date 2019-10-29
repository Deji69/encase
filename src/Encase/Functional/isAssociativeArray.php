<?php
namespace Encase\Functional;

/**
 * Check whether the given value is an indexed array.
 * An indexed array is qualified as an array with only integer keys, in no
 * particular sequence.
 *
 * @param  mixed $value
 * @return bool  True if `$value` is an indexed array, otherwise false.
 */
function isAssociativeArray($value) {
	if (!\is_array($value)) {
		return false;
	}
	return !isSequentialArray($value);
}
