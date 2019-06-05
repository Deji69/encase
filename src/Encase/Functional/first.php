<?php
namespace Encase\Functional;

/**
 * Get the value of the first element in `$iterable`.
 *
 * @param  \Traversable|iterable|string|stdClass|null
 * @return mixed|null  The first element in `$iterable` or null if empty.
 */
function first($iterable)
{
	$type = assertType($iterable, ['\Traversable', 'iterable', 'string', 'stdClass', 'null'], 'iterable');

	if (empty($iterable)) {
		return null;
	}

	if ($type === 'string') {
		return \mb_substr($iterable, 0, 1);
	}

	// Account for external iterators.
	if ($iterable instanceof \IteratorAggregate) {
		$iterable = $iterable->getIterator();
	}

	// Ensure iterators are valid so we return null rather than false like
	// end() does.
	if ($iterable instanceof \Iterator && !$iterable->valid()) {
		return null;
	}

	return \reset($iterable);
}
