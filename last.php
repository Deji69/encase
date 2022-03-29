<?php
namespace Encase\Functional;

/**
 * Get the value of the last element in `$iterable`.
 *
 * @param  \Traversable|iterable|string|stdClass|null
 * @return mixed|null  The last element in `$iterable` or null if empty.
 */
function last($iterable)
{
	$type = assertType($iterable, ['\Traversable', 'iterable', 'string', 'stdClass', 'null'], 'iterable');

	if (empty($iterable)) {
		return null;
	}

	if ($type === 'string') {
		return \mb_substr($iterable, -1);
	}

	// Account for external iterators.
	if ($iterable instanceof \IteratorAggregate) {
		$iterable = $iterable->getIterator();
	}

	// Ensure iterators are valid so we return null rather than false like
	// end() does. If it's an \ArrayIterator, we can treat it as an array, for
	// whatever reason...
	if ($iterable instanceof \Iterator) {
		if (!$iterable->valid()) {
			return null;
		}

		do {
			$last = $iterable->current();
			$iterable->next();
		} while ($iterable->valid());

		return $last;
	}

	return \end($iterable);
}
