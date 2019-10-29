<?php
namespace Encase\Functional;

/**
 * Check whether the given value is an sequential array.
 * A sequential array is qualified as an indexed array with sequential ordered
 * keys in the range of [0, `count($value)`).
 *
 * @param  mixed $value
 * @return bool  True if `$value` is an indexed array, otherwise false.
 */
function isSequentialArray($value)
{
	if (!\is_array($value)) {
		return false;
	}

	// Possibly premature micro-optimisation tests showed this foreach to be
	// miles faster than the double `\array_keys()` trick.
	$lastKey = -1;

	foreach (\array_keys($value) as $key) {
		// For some reason, using an incrementing index to compare to is a bit
		// slower than the "double array keys" method, but subtracting the
		// current and previous keys and comparing the result against 1 speeds
		// up the loop by roughly 200%.
		if (!\is_int($key) || ($key - $lastKey) != 1) {
			return false;
		}

		$lastKey = $key;
	}

	return true;
}
