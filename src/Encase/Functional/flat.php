<?php
namespace Encase\Functional;

/**
 *
 *
 * @param Traversable|array $iterable
 * @param int $depth [optional] Defaults to NULL (infinite depth).
 * @return array
 */
function flat(array $iterable, int $depth = null)
{
	$out = [];
	foreach ($iterable as $el) {
		if (\is_array($el)) {
			if ($depth === null || $depth > 1) {
				$el = flat($el, $depth !== null ? $depth - 1 : null);
			}

			$out = \array_merge($out, $el);
		} else {
			$out[] = $el;
		}
	}
	return $out;
}
