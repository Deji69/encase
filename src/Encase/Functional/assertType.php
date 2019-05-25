<?php
namespace Encase\Functional;

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
 * @throws \InvalidArgumentException
 */
function assertType($value, $type, string $paramName = null): string
{
	$match = isType($value, $type);

	if ($match === false) {
		$trace = \debug_backtrace(true, 2);
		$funcName = $trace[1]['function'];
		$refl = \function_exists($funcName) ?
			new \ReflectionFunction($funcName) :
			new \ReflectionMethod(...\explode('::', $funcName));
		$params = $refl->getParameters();
		$index = null;

		foreach ($params as $param) {
			if ($param->name === $paramName) {
				$index = $param->getPosition();
				break;
			}
		}

		$callerFile = $trace[1]['file'];
		$callerLine = $trace[1]['line'];

		if ($index === null) {
			$paramName = '$'.$paramName;
		} else {
			$paramName = $index.' ($'.$paramName.')';
		}

		$argument = 'Argument'.($paramName !== null ? ' '.$paramName : '');

		throw new \InvalidArgumentException(\sprintf(
			'%s of %s expects %s, %s given, called in %s on line %d',
			$argument,
			$funcName,
			join((array)$type, ', ', ' or '),
			\is_object($value) ? \get_class($value) : \gettype($value),
			$callerFile,
			$callerLine
		));
	}

	return $match;
}
