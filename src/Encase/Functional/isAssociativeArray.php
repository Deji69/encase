<?php
namespace Encase\Functional;

/**
 * Check whether the given value is an associative array.
 * An associative array is qualified as an array which is not sequential.
 *
 * @param  mixed $value
 * @return bool  True if `$value` is an indexed array, otherwise false.
 */
function isAssociativeArray($value) {
	if (!\is_array($value)) {
		return false;
	}
	if (empty($value)) {
		return true;
	}
	return !isSequentialArray($value);
}
