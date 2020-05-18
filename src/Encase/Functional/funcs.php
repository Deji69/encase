<?php
namespace Encase\Functional;

use Encase\Regex\Patternable;
use Encase\Functional\Exceptions\InvalidTypeError;
use ReflectionClass;

/**
 * Invoke the `$func` function on `$subject` with the given arguments.
 *
 * Calls `$func($subject, ...$args)`, but will also clone $subject if it is a
 * cloneable object, so as to prevent $func from being able to mutate it.
 * Returns the result of the function call.
 *
 * Note that the argument list passed is limited to the number of REQUIRED
 * function parameters, so as to allow PHP internal functions to be used with
 * greater flexibility and ease. This can be overridden by wrapping the `$func`
 * argument in an `\Encase\Functional\Func`, in which case all arguments will
 * be passed.
 *
 * @param  mixed  $subject The subject of the function invokation.
 * @param  callable  $func The function to apply.
 * @param  mixed  ...$args One or more arguments to pass to the function.
 * @return mixed  The result of the function call.
 */
function apply($subject, $func, ...$args)
{
	assertType($func, 'callable', 'func');

	if (\is_object($subject) && !($subject instanceof \Generator)) {
		$class = new ReflectionClass($subject);

		if ($class->isCloneable()) {
			$subject = clone $subject;
		}
	}

	\array_unshift($args, $subject);

	if (!$func instanceof Func) {
		$func = Func::new($func);

		if ($func->isInternal() && !$func->isVariadic()) {
			if ($nargs = $func->getNumberOfRequiredParameters()) {
				$args = \array_slice($args, 0, $nargs);
			}
		}
	}

	return \call_user_func_array($func, $args);
}

/**
 * Asserts the value is the given type or one of an array of types.
 * Returns the matched type of the value or throws an exception on no match.
 *
 * @see \Encase\Functional\isType
 *
 * @param  mixed  $value Value to be checked.
 * @param  string|string[]  $type Type or an array of matched types.
 * @param  string|null  $paramName Parameter name to reference in exceptions.
 * @return string
 * @throws \Encase\Functional\Exceptions\InvalidTypeError
 */
function assertType($value, $type, string $paramName = null): string
{
	$match = isType($value, $type);

	if ($match === false) {
		throw InvalidTypeError::make($type, $value, $paramName, 2);
	}

	return $match;
}

/**
 * Box a value into a fitting Functional wrapper class.
 *
 * @param  mixed  $value
 * @return Value|Str|Collection|Func
 */
function box($value = null)
{
	return Value::box($value);
}


/**
 * Concatenate one or more values to an iterable or string.
 *
 * @param  iterable|string  $container The container to append $values to.
 * @param  mixed            ...$values The values to append to $container.
 * @return iterable|string  Copy of $container with $values appended.
 */
function concat($container, ...$values)
{
	$type = assertType($container, ['string', 'iterable'], 'value');

	if ($type === 'string') {
		foreach ($values as $v) {
			$container .= $v;
		}
		return $container;
	}

	// Well, generators *are* iterables... I guess we can support them? (but SHOULD we?)
	if ($container instanceof \Generator) {
		$container = \iterator_to_array($container, true);
	} elseif (\is_object($container)) {
		$container = clone $container;
	}

	foreach ($values as $v) {
		$container[] = $v;
	}

	return $container;
}

/**
 * Calls `$func` on each element of `$iterable` with the elements value, key
 * and the `$iterable` as arguments.
 *
 * If `$iterable` is a string, it is split into unicode characters first and
 * the indicies are passed to `$func` as keys.
 *
 * `$func` may perform an early return by returning any non-null value if
 * `$earlyExit` is TRUE. The returned value will be returned by this function.
 *
 * @param  iterable|\stdClass|string $iterable
 * @param  mixed  $func Value where `isType($func, 'function')` is TRUE.
 * @param  bool  $earlyExit
 * @return null|mixed  A value returned by `$func` or null if no early return.
 */
function each($iterable, $func, $earlyExit = false)
{
	if (!empty($iterable)) {
		if ($type = assertType(
			$iterable,
			['iterable', 'stdClass', 'string'],
			'iterable'
		)) {
			if ($type === 'string') {
				$string = $iterable;
				$iterable = split($string);
			}

			foreach ($iterable as $key => $value) {
				$result = $func(
					$value,
					$key,
					$type === 'string' ? $string : $iterable
				);

				if ($earlyExit && $result !== null) {
					return $result;
				}
			}
		}
	}
	return null;
}

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

function fill($container, $value, int $size = null)
{
	$type = assertType($container, ['array', 'string', ['ArrayAccess', 'Countable']]);

	if ($size === null) {
		$size = count($container);
	}

	$funcOrGenerator = isType($value, ['function', 'Generator']);

	if ($type === 'string') {
		if (empty($value)) {
			return $container;
		}

		if (\is_string($value)) {
			return \str_pad('', $size, $value);
		}
	} elseif (!$funcOrGenerator) {
		return \array_fill(0, $size, $value);
	}

	$container = [];

	if ($funcOrGenerator === 'function') {
		$func = $value;
	} else {
		$func = function () use ($value) {
			return $value;
		};
	}

	for ($i = 0; $i < $size; ++$i) {
		$container[] = $func();
	}

	if ($type === 'string') {
		return join($container, '');
	}

	return $container;
}

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
		return each($iterable, $eachFn, true) ?? false;
	}

	if ($value instanceof \IteratorAggregate) {
		$iterator = $value->getIterator();
	} else {
		$iterator = new \ArrayIterator($value);
	}

	$iterable = new \LimitIterator($iterator, $offset);
	return each($iterable, $eachFn, true) ?? false;
}

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


/**
 * Reduce dimensions of an array from the shallow to deep end by unpacking
 * array elements in-place. Does not preserve keys.
 *
 * `$depth` can be used to limit the number of dimensions to recurse over.
 * If ommitted or NULL, the entire array will be unpacked to a single
 * dimension.
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

/**
 * Check whether the given value is an associative array.
 * An associative array is qualified as an array which is not sequential.
 *
 * @param  mixed $value
 * @return bool  True if `$value` is an indexed array, otherwise false.
 */
function isAssociativeArray($value) {
	if (!\is_array($value)) {
		return false;
	}
	if (empty($value)) {
		return true;
	}
	return !isSequentialArray($value);
}

/**
 * Check whether the given value is an indexed array.
 * An indexed array is qualified as an array with only integer keys in the
 * range of [0, `count($value)`), in no particular order.
 *
 * @param  mixed $value
 * @return bool  True if `$value` is an indexed array, otherwise false.
 */
function isIndexedArray($value)
{
	if (!\is_array($value)) {
		return false;
	}

	$max = \count($value);

	foreach (\array_keys($value) as $key) {
		if (!\is_int($key) || $key < 0 || $key >= $max) {
			return false;
		}
	}

	return true;
}

/**
 * Check whether the given value is an array containing integer keys.
 *
 * @param  mixed $value
 * @return bool  True if `$value` is a integer keyed array, otherwise false.
 */
function isIntKeyedArray($value)
{
	if (empty($value) || !\is_array($value)) {
		return false;
	}

	foreach (\array_keys($value) as $key) {
		if (\is_int($key)) {
			return true;
		}
	}

	return false;
}

/**
 * Check whether the given value is an sequential array.
 * A sequential array is qualified as an indexed array with sequential ordered
 * keys in the range of [0, `count($value)`).
 *
 * @param  mixed $value
 * @return bool  True if `$value` is an indexed array, otherwise false.
 */
function isSequentialArray($value)
{
	if (!\is_array($value)) {
		return false;
	}

	// Possibly premature micro-optimisation tests showed this foreach to be
	// miles faster than the double `\array_keys()` trick.
	$lastKey = -1;

	foreach (\array_keys($value) as $key) {
		// For some reason, using an incrementing index to compare to is a bit
		// slower than the "double array keys" method, but subtracting the
		// current and previous keys and comparing the result against 1 speeds
		// up the loop by roughly 200%.
		if (!\is_int($key) || ($key - $lastKey) != 1) {
			return false;
		}

		$lastKey = $key;
	}

	return true;
}

/**
 * Check whether the given value is an array containing string keys.
 *
 * @param  mixed $value
 * @return bool  True if `$value` is a string keyed array, otherwise false.
 */
function isStringKeyedArray($value)
{
	if (empty($value) || !\is_array($value)) {
		return false;
	}

	foreach (\array_keys($value) as $key) {
		if (\is_string($key)) {
			return true;
		}
	}

	return false;
}

/**
 * Check if the value has a given type or any of multiple given types, and
 * return the matched type.
 *
 * $type can be a type denoted by PHP's is_* functions (e.g. "iterable").
 * Additionally, it can be a class name, resulting in an instanceof check.
 * "function" is an additional type to distinguish from strings and arrays
 * that are ambiguously callable, which passes for \Encase\Functional\Func
 * or a \Closure.
 *
 * @param  mixed   $value The value to test.
 * @param  string|array  $type  The type, or array of types of which $value
 *                       must be any one of. A class name or one of: "array",
 *                       "bool", "callable", "countable", "double", "float",
 *                       "function", "int", "integer", "iterable", "long",
 *                       "null", "numeric", "object", "real", "resource",
 *                       "scalar", "string"
 * @return bool|string  Returns the matched type string or FALSE if none match.
 */
function isType($value, $type)
{
	if (!$type) {
		return false;
	}

	$types = (array)$type;
	$match = false;

	foreach ($types as $type) {
		if (\function_exists("is_$type")) {
			if (\call_user_func("is_$type", $value)) {
				$match = true;
			}
		} elseif ($type === 'function') {
			if ($value instanceof \Closure || $value instanceof Func) {
				$match = true;
			}
		} elseif ($type === 'countable') {  // polyfill for PHP <7.3
			if ((\is_array($value) || $value instanceof \Countable)) {
				$match = true;
			}
		} elseif ($value instanceof $type) {
			$match = true;
		}

		if ($match) {
			return $type;
		}
	}

	return false;
}

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

/**
 * Copy `$iterable` and call `$func` once for each iteration of iterable,
 * replacing values in the returned copy with the value returned from each call
 * to `$func`.
 *
 * Passes the value, key and original `$iterable` to $func on each iteration.
 *
 * To preserve array or object keys, pass true to `$preserveKeys`.
 *
 * `$iterable` may be mutated only if it is an object and `$func` mutates its
 * 4th parameter.
 *
 * @param  \Traversable|iterable|\stdClass|null  $iterable
 *         Array or object to iterate over.
 * @param  callable|\Encase\Functional\Func|null $func
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

/**
 * Get a callable returning the logical boolean inverse of the result of the
 * given predicate callable.
 *
 * @param callable $predicate
 * @return callable
 */
function not(callable $predicate): callable
{
	return function () use ($predicate) {
		return !$predicate(...\func_get_args());
	};
}

/**
 * Remove the last element from the container and return it.
 * This function takes the input by reference and changes its length.
 *
 * If used on a string, the string is treated as a sequence of unicode
 * characters in the default encoding and removes the last character.
 *
 * This function does not work with `\Generator`.
 *
 * @param  array|string|\ArrayAccess|\stdClass  $arrayish Array-like container.
 * @return mixed  The last element of the container.
 */
function pop(&$arrayish)
{
	$type = assertType(
		$arrayish,
		['array', '\ArrayAccess', '\Traversable', 'string', 'stdClass'],
		'type'
	);

	if ($type === 'array') {
		return \array_pop($arrayish);
	}

	if ($type === 'string') {
		$result = \mb_substr($arrayish, -1);
		$arrayish = \mb_substr($arrayish, 0, \mb_strlen($arrayish) - 1);
		return $result;
	}

	if ($type === '\ArrayAccess') {
		foreach ($arrayish as $key => $value) {
		}

		unset($arrayish[$key]);
	} else {
		$value = \end($arrayish);
		$key = \key($arrayish);
		unset($arrayish->$key);
	}
	return $value;
}

/**
 * Calls `$reducer` for each element of `$iterable`, using the return value
 * for the next call, ultimately returning the last result of the reducer.
 *
 * Each time `$reducer` is called, it is passed `$initial` or the return
 * value of the last call, followed by the current `$iterable` element value,
 * the element key, and the `$iterable` itself.
 *
 * If `$initial` is `null` then it is initialised with the first value in
 * `$iterable`, and `$reducer` is not called for it.
 *
 * If `$reducer` is `null`, a default reducer will be used based on the tpye of
 * `$initial`. For a numeric type, this will perform a sum of all elements. For
 * a string type, this will concatenate all elements. For an array type, this
 * will append all elements. Otherwise, the last element of `$iterable` is
 * returned.
 *
 * @param  iterable|\stdClass|string $iterable
 * @param  mixed $reducer The reducer function
 * @param  mixed $initial Initial value. If `null`, this will be initialised
 *                        with the first value in `$iterable` and `$reducer`
 *                        will not be called with it.
 * @return mixed
 */
function reduce($iterable, $reducer = null, $initial = null)
{
	if ($initial === null) {
		$initial = first($iterable);
		$initialSkip = true;
	}

	if ($reducer === null) {
		switch (isType($initial, ['string', 'numeric', 'array'])) {
			case 'numeric':
				$reducer = function ($current, $value) {
					return $current + $value;
				};
				break;
			case 'string':
				$reducer = function ($current, $value) {
					return $current . $value;
				};
				break;
			case 'array':
				$reducer = function ($current, $value) {
					$current[] = $value;
					return $current;
				};
				break;
			default:
				$reducer = function ($current, $value) {
					return $value;
				};
				break;
		}
	}

	each(
		$iterable,
		function ($value, $key, $iterable) use (&$initial, &$initialSkip, $reducer) {
			if ($initialSkip) {
				$initialSkip = false;
			} else {
				$initial = $reducer($initial, $value, $key, $iterable);
			}
		}
	);

	return $initial;
}

/**
 * Remove the first element from the container and return it.
 * This function takes the input by reference and changes its length.
 *
 * If used on a string, the string is treated as a sequence of unicode
 * characters in the default encoding and removes the first character.
 *
 * @param  array|string|\ArrayAccess|\stdClass  $arrayish Array-like container.
 * @return mixed  The first element of the container.
 */
function shift(&$arrayish)
{
	$type = assertType(
		$arrayish,
		['array', '\ArrayAccess', 'string', 'stdClass'],
		'type'
	);

	if ($type === 'array') {
		return \array_shift($arrayish);
	}

	if ($type === 'string') {
		$result = \mb_substr($arrayish, 0, 1);
		$arrayish = \mb_substr($arrayish, 1);
		return $result;
	}

	foreach ($arrayish as $key => $value) {
		break;
	}

	if ($type === '\ArrayAccess') {
		unset($arrayish[$key]);
	} else {
		unset($arrayish->$key);
	}
	return $value;
}

/**
 * Get the size of `$value`.
 *
 * For iterables, this is the number of elements.
 * For strings, this is the number of characters.
 *
 * @param  iterable|string  $value
 * @return int  Size of `$value` - 0 if `$value` is not an interable or string.
 *
 * @alias count
 */
function size($value)
{
	if (\is_string($value)) {
		return \function_exists('mb_strlen') ?
			\mb_strlen($value) :
			\count(\preg_split('//u', $value, null, PREG_SPLIT_NO_EMPTY));
	}

	if (isType($value, ['array', 'Countable'])) {
		return \count($value);
	}

	$size = 0;

	if (isType($value, ['iterable', 'stdClass'])) {
		foreach ($value as $v) {
			++$size;
		}
		return $size;
	}

	return $size;
}

function count()
{
	return size(...\func_get_args());
}

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
 * @return array|string  A slice of $value.
 */
function slice($value, ?int $start, int $end = null)
{
	$type = assertType($value, ['string', 'iterable', '\Traversable', '\stdClass'], 'value');

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
	if (\is_object($value)) {
		// Try to use PHP's iterator_to_array for classes implementing
		// \IteratorAggreggate.
		if ($value instanceof \IteratorAggregate && $start > 0) {
			if ($size <= 0) {
				$size = -1;
			}

			$value = \iterator_to_array(
				new \LimitIterator($value->getIterator(), $start, $size),
				true
			);

			$start = 0;
		} else {
			// Fall back to a foreach by building an array out of the iterator. If
			// we have a positive $size we can stop early and return that array.
			$output = [];

			foreach ($value as $key => $val) {
				if ($start > 0) {
					--$start;
					continue;
				}

				$output[$key] = $val;

				if ($size !== null && $size > 0) {
					--$size;

					if (!$size) {
						return $output;
					}
				}
			}

			return $output;
		}
	}

	return \array_slice($value, $start, $size, true);
}

/**
 * Split a string up into an array of strings.
 *
 * @param  string  $str Input string.
 * @param  string|\Encase\Regex\Regex  $separator Denotes where the splits
 *                 should occur. An empty string will result in the whole
 *                 string being returned as an array of single characters.
 *                 A Regex object can be passed to split based on a pattern.
 * @param  int     $limit Limit for the resulting array size.
 * @return array
 */
function split(string $str, $separator = '', int $limit = null): array
{
	assertType($str, 'string', 'str');
	assertType($separator, ['string', Patternable::class], 'separator');

	if (empty($separator)) {
		return \preg_split('//u', $str, $limit, PREG_SPLIT_NO_EMPTY);
	}

	if ($separator instanceof Patternable) {
		$separator = $separator->getPattern();

		if (!\function_exists('mb_split')) {
			return \preg_split($separator, $str, $limit);
		}

		$end = \strrpos($separator, $separator[0]);
		$separator = \substr($separator, 1, $end - 1);
	} elseif (!\function_exists('mb_split')) {
		return \explode($separator, $str, $limit);
	} else {
		$separator = \preg_quote($separator);
	}

	return \mb_split($separator, $str, $limit ?? -1);
}

/**
 * Get a number of elements from the beginning of the iterable into an array.
 *
 * @param string|iterable|\stdClass $iterable
 * @param int $count
 * @return array|string
 */
function take($iterable, $count)
{
	return slice($iterable, 0, $count);
}

/**
 * Get each value of `$iterable` until `$predicate` returns falsey.
 *
 * @param  string|iterable|\stdClass
 * @return array|string
 */
function takeUntil($iterable, callable $predicate)
{
	return takeWhile($iterable, not($predicate));
}

/**
 * Get each value of `$itearble` while `$predicate` returns truthy.
 *
 * @param  string|iterable|\stdClass
 * @return array
 */
function takeWhile($iterable, callable $predicate)
{
	$values = [];

	each(
		$iterable,
		function ($value, $key) use ($predicate, &$values) {
			if (!$predicate(...\func_get_args())) {
				return null;
			}

			$values[$key] = \is_object($value) ? clone $value : $value;
		}
	);

	return $values;
}

/**
 * Converts array-like objects into arrays.
 * Any objects stored within the elements are cloned to preserve immutability.
 *
 * @param  string|\Traversable|\stdClass $value The object to convert.
 * @return array Empty array if `$value` is not iterable.
 */
function toArray($value)
{
	if (\is_string($value)) {
		return split($value);
	}
	if (isType($value, ['Traversable', 'stdClass'])) {
		$array = [];

		foreach ($value as $key => $val) {
			$array[$key] = \is_object($val) ? clone $val : $val;
		}

		return $array;
	}
	return (array)$value;
}

/**
 * Get a Type representing the given type or object class.
 *
 * @param string $typeOrClass
 * @param string $class
 * @return Type
 */
function type($typeOrClass, $class = null): Type
{
	return new Type($typeOrClass, $class);
}

/**
 * Get the type of a variable.
 * Based on which of PHP's is_* checks returns true rather than using gettype.
 *
 * @param  mixed  $value
 * @return string|null  One of: "array", "bool", "int", "float", "function",
 *                      "null", "object", "resource", "string",
 *                      or NULL if the type is unknown.
 */
function typeOf($value): ?string
{
	if (\is_array($value)) {
		return 'array';
	} elseif (\is_bool($value)) {
		return 'bool';
	} elseif (\is_float($value)) {
		return 'float';
	} elseif (\is_int($value)) {
		return 'int';
	} elseif (\is_null($value)) {
		return 'null';
	} elseif (\is_object($value)) {
		return 'object';
	} elseif (\is_resource($value)) {
		return 'resource';
	} elseif (\is_string($value)) {
		return 'string';
	}
	return null;
}

/**
 * Produce an array containing unique elements of each given arrayish value.
 * Uniqueness considers string keys but numeric keys collide.
 * Later values overwrite previous ones in the case of string keys.
 *
 * @param  \array
 * @return \array
 */
function union(...$iterables)
{
	$values = \array_merge(...map(
		$iterables,
		\Closure::fromCallable('Encase\Functional\toArray')
	));
	if (isIndexedArray($values)) {
		$values = \array_unique($values);
		$values = \array_values($values);
	} else {
		$values = unique($values, true);
	}
	return $values;
}

/**
 * Get the unique elements of an array or object.
 * Will return an array containing the keys and values of the first occurrence
 * of each unique value in the array.
 *
 * @param  array|\Traversable|iterable|\stdClass $iterable
 * @param  bool $assoc If TRUE, preserves duplicate values having string keys.
 * @return array
 */
function unique($iterable, bool $keepKeyed = false, int $sortFlags = \SORT_REGULAR)
{
	$type = assertType($iterable, [
		'array', 'string', 'iterable', '\Traversable', 'stdClass'
	], 'iterable');

	if ($keepKeyed) {
		$indexed = [];
		$keyed = [];

		foreach ($iterable as $k => $v) {
			if (\is_int($k)) {
				$indexed[] = $v;
			} else {
				$keyed[$k] = $v;
			}
		}

		return \array_values(\array_unique($indexed)) + $keyed;
	}

	$iterable = toArray($iterable);
	$iterable = \array_unique($iterable, $sortFlags);

	if ($type === 'string') {
		$iterable = join($iterable, '');
	}

	return $iterable;
}

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
