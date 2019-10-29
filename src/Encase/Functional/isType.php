<?php
namespace Encase\Functional;

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
