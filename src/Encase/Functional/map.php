<?php
namespace Encase\Functional;

/**
 * Copy `$iterable` and call `$func` once for each iteration of iterable,
 * replacing values in the returned copy with the value returned from each call
 * to `$func`.
 *
 * Passes the value, key and original `$iterable` to $func on each iteration.
 *
 * To preserve array or object keys, pass true to `$preserveKeys`.
 *
 * @param  \Traversable|iterable|\stdClass|null  $iterable
 *         Array or object to iterate over.
 * @param  callable|\Encase\Functional\Func|null
 * @param  bool  $preserveKeys
 *         Set to true to preserve keys.
 * @return mixed  New object or array with the same type as $iterable, or an
 *                array if $iterable does not implement \ArrayAccess.
 */
function map($iterable, $func = null, bool $preserveKeys = false)
{
	assertType($iterable, ['\Traversable', 'iterable', 'stdClass', 'null'], 'iterable');

	if (\is_object($iterable)) {
		if ($iterable instanceof \ArrayIterator) {
			$output = new \ArrayObject($iterable->getArrayCopy());
		} elseif ($iterable instanceof \ArrayAccess) {
			$output = clone $iterable;
		} else {
			$output = (array)$iterable;
		}
	} else {
		$output = empty($iterable) || !$preserveKeys ? [] : $iterable;
	}

	if (!empty($iterable)) {
		foreach ($iterable as $key => $value) {
			$value = $func ? apply($value, $func, $key, $iterable) : $value;

			if ($preserveKeys) {
				$output[$key] = $value;
			} else {
				unset($output[$key]);
				$output[] = $value;
			}
		}
	}

	return $output;
}
