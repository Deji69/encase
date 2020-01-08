<?php
namespace Encase\Functional;

/**
 * Searches forward through the value for a given sub-value.
 *
 * Returns an array containing index/key and the value if found, boolean FALSE
 * is returned if no match is found. Indexes are returned for indexed arrays
 * and strings, while string keys are returned for objects and assoc arrays.
 *
 * `$pred` can be a value, in which case a match is found using strict
 * comparison in the case of arrays. In the case of strings, PHP's strpos is
 * used, with which any value not a string will be converted to an int and
 * tested as an ordinal value of the character.
 *
 * If `$pred` is a `\Closure` or `\Encase\Functional\Func`, then the predicate
 * is always used. However, due to ambiguity in PHP callables, a string or
 * array predicate will not be treated as a function if `$value` is a `string`
 * or `array`. Prefer to always use `\Encase\Functional\Func` for callables
 * that are not closures.
 *
 * This function attempts to use the native PHP functions `array_search` and
 * `mb_strpos` when the predicate is not unambiguously a function.
 *
 * @param  array|string|iterable|stdClass  $value Value to search in.
 * @param  mixed|\Closure|\Encase\Functional\Func  $pred
 *         A predicate function to perform the comparison or a value.
 *         If null, the first truthy value will be returned.
 * @param  int    $offset Index to begin searching at.
 * @return array|bool  Returns [index/key, value] if found, otherwise FALSE.
 */
function find($value, $pred = null, int $offset = 0)
{
	$type = assertType($value, ['\Traversable', 'iterable', 'string', 'stdClass', 'null'], 'value');

	if (empty($value)) {
		return false;
	}

	// Prevent also passing the index to PHP internal functions.
	if ($pred instanceof Func && $pred->isInternal()) {
		$pred = function ($value) use ($pred) {
			return $pred($value);
		};
	} elseif (!isType($pred, 'function')) {
		if ($pred === null) {
			$pred = function ($value) {
				return $value != false;
			};
		}

		if (\is_array($value)) {
			if (!\is_callable($pred)) {
				if ($offset) {
					$value = \array_slice($value, $offset, null, true);
				}

				$key = \array_search($pred, $value, true);
				return $key !== false ? [$key, $value[$key]] : false;
			}
		} elseif ($type === 'string' && !\function_exists('mb_strpos')) {
			$pos = \mb_strpos($value, $pred, $offset);
			return $pos !== false ? [$pos, \mb_substr($value, $pos, 1)] : false;
		} else {
			$pred = function ($value) use ($pred) {
				return $value === $pred;
			};
		}
	}

	$eachFn = function ($char, $index) use ($pred) {
		if ($pred($char, $index)) {
			return [$index, $char];
		}
	};

	if ($type === 'string') {
 		$iterable = new \LimitIterator(new \ArrayIterator(split($value)), $offset);
		return each($iterable, $eachFn) ?? false;
	}

	if ($value instanceof \IteratorAggregate) {
		$iterator = $value->getIterator();
	} else {
		$iterator = new \ArrayIterator($value);
	}

	$iterable = new \LimitIterator($iterator, $offset);
	return each($iterable, $eachFn) ?? false;
}
