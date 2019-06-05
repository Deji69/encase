<?php
namespace Encase\Functional\Exceptions;

use function Encase\Functional\join;

class InvalidTypeError extends \InvalidArgumentException
{
	public static function make($type, $value, $paramName, $depth = 1)
	{
		$trace = \debug_backtrace(true, $depth + 1);
		$funcName = $trace[$depth]['function'];
		$index = null;
		$type = \array_unique((array)$type);

		if (\function_exists($funcName)) {
			$refl = new \ReflectionFunction($funcName);
		} else {
			$method = \explode('::', $funcName);

			if (count($method) < 2) {
				\array_unshift($method, $trace[$depth]['class']);
			}

			$refl = new \ReflectionMethod(...$method);
		}

		$params = $refl->getParameters();

		foreach ($params as $param) {
			if ($param->name === $paramName) {
				$index = $param->getPosition();
				break;
			}
		}

		$callerFile = $trace[$depth]['file'];
		$callerLine = $trace[$depth]['line'];

		if ($index === null) {
			$paramName = '$'.$paramName;
		} else {
			$paramName = $index.' ($'.$paramName.')';
		}

		$argument = 'Argument'.($paramName !== null ? ' '.$paramName : '');

		return new self(\sprintf(
			'%s of %s expects %s, %s given, called in %s on line %d',
			$argument,
			$funcName,
			join((array)$type, ', ', ' or '),
			\is_object($value) ? \get_class($value) : \gettype($value),
			$callerFile,
			$callerLine
		));
	}
}
