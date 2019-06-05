<?php
namespace Encase\Functional;

/**
 * Extract a portion of an array, string or \Traversable object.
 * Sliced traversables are returned as arrays.
 *
 * Will try to use native PHP functions where possible.
 *
 * @see https://php.net/manual/en/function.array-slice.php
 * @see https://php.net/manual/en/function.mb-substr.php
 * @see https://php.net/manual/en/function.iterator-to-array.php
 *
 * @param  array|string  $value
 * @param  int|null      $start Where to begin extraction.
 * @param  int|null      $size  Where to end extraction.
 * @return mixed   A slice of $value.
 */
function slice($value, ?int $start, int $end = null)
{
	$type = assertType($value, ['\Traversable', 'iterable', 'string'], 'value');

	$start = $start ?? 0;

	// Figure out the slice size as PHP funcs expect sizes rather than indexes.
	if ($end !== null) {
		if ($start && ($end > 0 || $start < 0) && $end < $start) {
			[$start, $end] = [$end, $start];
		}

		$size = $end < 0 ? $end : $end - $start;
	} else {
		$size = null;
	}

	// Implement strings using unicode-aware (where possible) substr.
	// TODO: make mb_* a requirement of the library?
	if ($type === 'string') {
		return \function_exists('mb_substr') ?
			\mb_substr($value, $start, $size) :
			\substr($value, $start, $size);
	}

	// If the value is traversable, we can extract with a foreach loop.
	if ($type === '\Traversable') {
		// Try to use PHP's iterator_to_array for classes implementing
		// \IteratorAggreggate.
		if ($value instanceof \IteratorAggregate && $start > 0) {
			if ($size <= 0) {
				$size = -1;
			}

			$value = \iterator_to_array(
				new \LimitIterator($value->getIterator(), $start, $size), true
			);

			$start = 0;
		} else {
			// Fall back to a foreach by building an array out of the iterator. If
			// we have a positive $size we can stop early and return that array.
			// If not, we'll fall back with array_slice with a $start of 0.
			$output = [];

			foreach ($value as $key => $value) {
				if ($start > 0) {
					--$start;
					continue;
				}

				$output[$key] = $value;

				if ($size !== null && $size > 0) {
					--$size;

					if (!$size) {
						return $output;
					}
				}
			}

			$value = $output;
		}
	}

	return \array_slice($value, $start, $size, true);
}
