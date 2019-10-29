<?php
namespace Encase\Functional;

function fill($container, $value, int $size = null)
{
	$type = assertType($container, ['array', 'string', ['ArrayAccess', 'Countable']]);

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

	if ($funcOrGenerator === 'function') {
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
