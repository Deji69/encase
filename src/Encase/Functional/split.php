<?php
namespace Encase\Functional;

use Encase\Regex\Patternable;

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
