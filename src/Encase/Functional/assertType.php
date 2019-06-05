<?php
namespace Encase\Functional;

use Encase\Functional\Exceptions\InvalidTypeError;

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
