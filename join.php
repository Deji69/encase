<?php
namespace Encase\Functional;

/**
 * Get a string by concatenting all the iterable elements, separated by a given
 * separator.
 *
 * If `$lastSeparator` is specified, it will be used instead of `$separator` to
 * separate the last two elements in the string, or both elements if the
 * `$iterable` only has two. Can be used to build gramatically correct lists.
 *
 * @param  iterable|array  $iterable
 * @param  string|null  $separator  Used to separate sequential elements in the
 *                                  string.
 * @param  string|null  $lastSeparator Used to separate the last two elements
 *                                     in the string.
 * @return string
 */
function join($iterable, ?string $separator = ',', string $lastSeparator = null): string
{
	assertType($iterable, ['iterable', 'stdClass'], 'iterable');
	$separator = $separator ?? ',';

	if ($iterable instanceof \Traversable) {
		$iterable = \iterator_to_array($iterable);
	} elseif (\is_object($iterable)) {
		$iterable = (array)$iterable;
	}

	if ($lastSeparator !== null && \count($iterable) > 1) {
		$lastTwo = \array_splice($iterable, -2);
		$iterable[] = \implode($lastSeparator, $lastTwo);
	}

	return \implode($separator, $iterable);
}
