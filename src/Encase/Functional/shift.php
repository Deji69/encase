<?php
namespace Encase\Functional;

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
