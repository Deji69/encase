<?php
namespace Encase\Functional;

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
