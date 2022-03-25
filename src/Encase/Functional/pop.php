<?php
namespace Encase\Functional;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;

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
		if (!$arrayish instanceof \IteratorAggregate || $arrayish instanceof ArrayAccess) {
			foreach ($arrayish as $key => $value) {
			}
			unset($arrayish[$key]);
			return $value;
		}

		if (!$arrayish instanceof ArrayObject) {
			$vars = array_keys((array)$arrayish);
			$key = \end($vars);
			$value = $arrayish[$key];
			unset($arrayish[$key]);
			return $value;
		}
	}

	if (\is_object($arrayish)) {
		$obj = new \ArrayObject($arrayish);
		foreach ($obj as $key => $value) {
		}
		unset($arrayish->$key);
		return $value;
	} else {
		$value = \end($arrayish);
		$key = \key($arrayish);
		unset($arrayish[$key]);
	}

	return $value;
}
