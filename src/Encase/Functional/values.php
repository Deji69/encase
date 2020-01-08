<?php
namespace Encase\Functional;

/**
 * Re-index the traversable, array or object. Equivalent to calling
 * `map($iterable)`.
 *
 * @param  \Traversable|iterable|stdClass|null $iterable
 * @return $iterable
 */
function values($iterable)
{
	assertType($iterable, ['\Traversable', 'iterable', 'stdClass', 'null'], 'iterable');
	return map($iterable);
}
