<?php
namespace Encase\Functional;

/**
 * Get the size of `$value`.
 *
 * For iterables, this is the number of elements.
 * For strings, this is the number of characters.
 *
 * @param  iterable|string  $value
 * @param  string $encoding (optional) The character encoding of `$value` if
 *                          it's a string; see `mb_list_encodings()` for the
 *                          list of supported encodings.
 * @return int  Size of `$value` - 0 if `$value` is not an interable or string.
 *
 * @alias count
 */
function size($value, string $encoding = 'UTF-8')
{
	if (isType($value, ['array', 'Countable'])) {
		return \count($value);
	}

	if (\is_string($value)) {
		return \function_exists('mb_strlen') ?
			\mb_strlen($value, $encoding) :
			\count(\preg_split('//u', $value, null, PREG_SPLIT_NO_EMPTY));
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