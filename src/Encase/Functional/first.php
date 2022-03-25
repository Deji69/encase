<?php
namespace Encase\Functional;

use ArrayIterator;

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

	// (PHP8+) Account for objects.
	if (\is_object($iterable)) {
		$iterable = new ArrayIterator($iterable);
	}

	// Ensure iterators are valid so we return null rather than false like
	// end() does. If it's an \ArrayIterator, we can treat it as an array, for
	// whatever reason...
	if ($iterable instanceof \Iterator) {
		if (!$iterable->valid()) {
			return null;
		}

		$iterable->rewind();
		return $iterable->current();
	}

	return \reset($iterable);
}
