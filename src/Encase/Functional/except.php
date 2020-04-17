<?php
namespace Encase\Functional;

/**
 * Copy `$iterable`, minus elements matching a predicate.
 *
 * If `$pred` is a function, it is called with the value, key and original
 * `$iterable`, and the element is omitted from copy if the call returns truthy.
 * If `$pred` is not a function, it is checked against each element using
 * strict equality. Matching elements are excluded.
 *
 * @param  \Traversable|iterable|\stdClass|null  $iterable
 *         Array or object to iterate over.
 * @param  mixed $pred
 * @param  bool  $preserveKeys
 *         Set to true to preserve keys.
 * @return mixed  New object or array with the same type as $iterable, or an
 *                array if $iterable does not implement \ArrayAccess.
 */
function except($iterable, $pred = null, bool $preserveKeys = false)
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
		$predIsFunc = isType($pred, 'function');

		foreach ($iterable as $key => $value) {
			if ($predIsFunc ? !apply($value, $pred, $key, $iterable) : $pred !== $value) {
				if ($preserveKeys) {
					$output[$key] = $value;
				} else {
					unset($output[$key]);
					$output[] = $value;
				}
			}
		}
	}

	return $output;
}
