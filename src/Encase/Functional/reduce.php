<?php
namespace Encase\Functional;

/**
 * Calls `$reducer` for each element of `$iterable`, using the return value
 * for the next call, ultimately returning the last result of the reducer.
 *
 * Each time `$reducer` is called, it is passed `$initial` or the return
 * value of the last call, followed by the current `$iterable` element value,
 * the element key, and the `$iterable` itself.
 *
 * If `$initial` is `null` then it is initialised with the first value in
 * `$iterable`, and `$reducer` is not called for it.
 *
 * If `$reducer` is `null`, a default reducer will be used based on the tpye of
 * `$initial`. For a numeric type, this will perform a sum of all elements. For
 * a string type, this will concatenate all elements. For an array type, this
 * will append all elements. Otherwise, the last element of `$iterable` is
 * returned.
 *
 * @param  iterable|\stdClass|string $iterable
 * @param  mixed $reducer The reducer function
 * @param  mixed $initial Initial value. If `null`, this will be initialised
 *                        with the first value in `$iterable` and `$reducer`
 *                        will not be called with it.
 * @return mixed
 */
function reduce($iterable, $reducer = null, $initial = null)
{
	if ($initial === null) {
		$initial = first($iterable);
		$initialSkip = true;
	}

	if ($reducer === null) {
		switch (isType($initial, ['string', 'numeric', 'array'])) {
			case 'numeric':
				$reducer = function ($current, $value) {
					return $current + $value;
				};
				break;
			case 'string':
				$reducer = function ($current, $value) {
					return $current . $value;
				};
				break;
			case 'array':
				$reducer = function ($current, $value) {
					$current[] = $value;
					return $current;
				};
				break;
			default:
				$reducer = function ($current, $value) {
					return $value;
				};
				break;
		}
	}

	each(
		$iterable,
		function ($value, $key, $iterable) use (&$initial, &$initialSkip, $reducer) {
			if ($initialSkip) {
				$initialSkip = false;
			} else {
				$initial = $reducer($initial, $value, $key, $iterable);
			}
		}
	);

	return $initial;
}
