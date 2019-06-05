<?php
namespace Encase\Functional;

/**
 * Get the type of a variable.
 * Based on which of PHP's is_* checks returns true rather than using gettype.
 * Returns "function" for closure objects but does not work with other
 * callables as those are strings and arrays first.
 *
 * @param  mixed  $value
 * @return string|null  One of: "array", "bool", "int", float", "function",
 *                      "null", "object", "resource", "string"
 */
function type($value): string
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
		return $value instanceof \Closure ? 'function' : 'object';
	} elseif (\is_resource($value)) {
		return 'resource';
	} elseif (\is_string($value)) {
		return 'string';
	}
	return 'unknown type';
}
