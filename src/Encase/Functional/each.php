<?php
namespace Encase\Functional;

/**
 * Calls `$func` on each element of `$iterable` with the elements value, key
 * and the `$iterable` as arguments.
 *
 * If `$iterable` is a string, it is split into unicode characters first and
 * the indicies are passed to `$func` as keys.
 *
 * `$func` may perform an early return by returning any non-null value if
 * `$earlyExit` is TRUE. The returned value will be returned by this function.
 *
 * @param  iterable|\stdClass|string $iterable
 * @param  mixed  $func Value where `isType($func, 'function')` is TRUE.
 * @param  bool  $earlyExit
 * @return null|mixed  A value returned by `$func` or null if no early return.
 */
function each($iterable, $func, $earlyExit = false)
{
	if (!empty($iterable)) {
		if ($type = assertType(
			$iterable,
			['iterable', 'stdClass', 'string'],
			'iterable'
		)) {
			if ($type === 'string') {
				$string = $iterable;
				$iterable = split($string);
			}

			foreach ($iterable as $key => $value) {
				$result = $func(
					$value,
					$key,
					$type === 'string' ? $string : $iterable
				);

				if ($earlyExit && $result !== null) {
					return $result;
				}
			}
		}
	}
	return null;
}
