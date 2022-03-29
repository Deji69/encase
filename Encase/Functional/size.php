<?php
namespace Encase\Functional;

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
