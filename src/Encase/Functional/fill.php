<?php
namespace Encase\Functional;

function fill($container, $value, $size = null)
{
	$type = assertType($container, ['array', 'string']);

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

	if ($funcOrGenerator === 'Generator') {
		$func = new Func($value);
	} elseif ($funcOrGenerator === 'function') {
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
