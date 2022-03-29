<?php
namespace Encase\Functional;

/**
 * Produce an array containing unique elements of each given arrayish value.
 * Uniqueness considers string keys but numeric keys collide.
 * Later values overwrite previous ones in the case of string keys.
 *
 * @param  \array
 * @return \array
 */
function union(...$iterables)
{
	$values = \array_merge(...map(
		$iterables,
		\Closure::fromCallable('Encase\Functional\toArray')
	));
	if (isIndexedArray($values)) {
		$values = \array_unique($values);
		\sort($values);
		$values = \array_values($values);
	} else {
		$values = unique($values, true);
		\ksort($values, SORT_STRING);
	}
	return $values;
}
