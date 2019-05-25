<?php
namespace Encase\Functional;

/**
 * Calls $func on each element of $iterable with the elements value, key and
 * the $iterable as arguments.
 *
 * If $iterable is a string, it is split into unicode characters first and the
 * indicies are passed to $func as keys.
 *
 * Any non-null return value of $func will end the loop early and its value
 * will be returned by this function.
 *
 * @param  iterable|stdClass|string
 * @param  mixed  $func Value that returns true for isType($func, 'function').
 * @return null|mixed  A value returned by $func or null if no return.
 */
function each($iterable, $func)
{
	$type = assertType($iterable, ['iterable', 'stdClass', 'string', 'null'], 'iterable');

	if (!empty($iterable)) {
		if ($type === 'string') {
			$string = $iterable;
			$iterable = split($string);
		}

		foreach ($iterable as $key => $value) {
			$result = $func($value, $key, $type === 'string' ? $string : $iterable);

			if ($result !== null) {
				return $result;
			}
		}
	}

	return null;
}
