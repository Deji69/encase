<?php
namespace Encase\Functional;

function take($iterable, $count)
{
	return slice($iterable, 0, $count);
}

/**
 * Get each value of `$iterable` until `$predicate` returns falsey.
 *
 * @param  string|iterable|\stdClass
 * @return array
 */
function takeUntil($iterable, callable $predicate)
{
	return takeWhile($iterable, not($predicate));
}

/**
 * Get each value of `$itearble` while `$predicate` returns truthy.
 *
 * @param  string|iterable|\stdClass
 * @return array
 */
function takeWhile($iterable, callable $predicate)
{
	$values = [];

	each(
		$iterable,
		function ($value, $key) use ($predicate, &$values) {
			if (!$predicate(...\func_get_args())) {
				return null;
			}

			$values[$key] = \is_object($value) ? clone $value : $value;
		}
	);

	return $values;
}
