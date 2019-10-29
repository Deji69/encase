<?php
namespace Encase\Functional;

/**
 * Combine multiple array-like objects into one.
 *
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
