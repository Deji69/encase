<?php
namespace Encase\Functional;

/**
 * Concatenate one or more values to an iterable or string.
 *
 * @param  iterable|string  $container The container to append $values to.
 * @param  mixed            ...$values The values to append to $container.
 * @return iterable|string  Copy of $container with $values appended.
 */
function concat($container, ...$values)
{
	$type = assertType($container, ['string', 'iterable'], 'value');

	if ($type === 'string') {
		foreach ($values as $v) {
			$container .= $v;
		}
		return $container;
	}

	// Well, generators *are* iterables... I guess we can support them? (but SHOULD we?)
	if ($container instanceof \Generator) {
		$container = \iterator_to_array($container, true);
	} elseif (\is_object($container)) {
		$container = clone $container;
	}

	foreach ($values as $v) {
		$container[] = $v;
	}

	return $container;
}
