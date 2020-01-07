<?php
namespace Encase\Functional;

/**
 * Calls `$predicate` for each elemnt of `$iterable`, using the return value
 * for the next call, ultimately returning the last result of the predicate.
 *
 * Each time `$predicate` is called, it is passed `$initial` or the return
 * value of the last call, followed by the current `$iterable` element value,
 * the element key, and the `$iterable` itself.
 *
 * If `$predicate` is not provided, a default predicate will be used based on
 * the type of `$initial`. For a numeric type, this will perform a sum of all
 * elements. For a string type, this will concatenate all elements. For an
 * array type, this will append all elements. Otherwise, the last element of
 * `$iterable` is returned.
 *
 * @param  iterable|\stdClass|string $iterable
 * @param  mixed $initial Value initially passed to `$predicate`
 * @param  mixed $predicate Function which transforms `$initial`
 * @return mixed
 */
function reduce($iterable, $initial = null, $predicate = null)
{
	if ($predicate === null) {
		switch (isType('numeric', 'string', 'array')) {
			case 'numeric':
				$predicate = function ($current, $value) {
					return $current + $value;
				};
			break;
			case 'string':
				$predicate = function ($current, $value) {
					return $current . $value;
				};
			break;
			case 'array':
				$predicate = function ($current, $value) {
					$current[] = $value;
					return $current;
				};
			break;
			default:
				$predicate = function ($current, $value) {
					return $value;
				};
			break;
		}
	}

	each(
		$iterable,
		function ($value, $key, $iterable) use (&$initial, $predicate) {
			$initial = $predicate($initial, $value, $key, $iterable);
		}
	);

	return $initial;
}
