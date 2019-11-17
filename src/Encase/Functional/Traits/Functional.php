<?php
namespace Encase\Functional\Traits;

use function Encase\Functional\isType;

/**
 * Proxies method calls to \Encase\Functional functions.
 *
 * @method mixed  apply(callable $function)
 * @method self   concat(mixed ...$values)
 * @method int    count()
 * @method $this  each(callable $function)
 * @method self   fill($value, int $length = null)
 * @method array|false  find(mixed $predOrValue, int $offset)
 * @method mixed  first()
 * @method bool   isType(string|array $type)
 * @method string join(?string $separator = ',', string $lastSeparator = null)
 * @method mixed  last()
 * @method self   map(callable $function, bool $preserveKeys = false)
 * @method mixed  pop()
 * @method mixed  shift()
 * @method int    size()
 * @method self   slice(?int $begin, int $end = null)
 * @method array  split(string $separator = '', int $limit = null)
 * @method string type()
 * @method array  union(...$arrayish)
 * @method self   unique(bool $keepKeyed = false, int $sortFlags = \SORT_REGULAR)
 * @method self   values()
 */
trait Functional
{
	/**
	 * Functions that shouldn't be callable as instance methods.
	 *
	 * @var array
	 */
	private static $excludeFunctions = [
		'assertType', 'box'
	];

	/**
	 * Functions that return mutated versions of their input.
	 *
	 * @var array
	 */
	private static $mutatingFunctions = [
		'map', 'slice', 'split', 'transform',
		'apply', 'concat',
		'unique', 'union'
	];

	/**
	 * Functions that return their input unmutated.
	 * For these we can safely return the current instance in method calls.
	 *
	 * @var array
	 */
	private static $tappedFunctions = [
	];

	/**
	 * Call a Functional function using this instance as the first argument.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return static|$this
	 */
	public function __call($method, $parameters)
	{
		return $this->callFunctionalMethod($this, $method, $parameters);
	}

	/**
	 * Check if the given method will mutate its subject.
	 *
	 * @param  string  $method
	 * @return bool
	 */
	protected function isMethodAMutator(string $method): bool
	{
		return \in_array($method, self::$mutatingFunctions);
	}

	/**
	 * Check if the given method will return its first subject argument.
	 *
	 * @param  string  $method
	 * @return bool
	 */
	protected function isMethodTapped(string $method): bool
	{
		return \in_array($method, self::$tappedFunctions);
	}

	/**
	 * Call a Functional function using $subject as the first argument.
	 * Classes using this trait can override __call and use this to carry out
	 * functions on an object the class owns.
	 *
	 * @param  mixed   $subject
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return static|self|$this
	 */
	protected function callFunctionalMethod(&$subject, $method, $parameters)
	{
		$function = 'Encase\\Functional\\'.$method;

		if (!\in_array($method, self::$excludeFunctions) && \function_exists($function)) {
			$result = $function($subject, ...$parameters);

			if ($this->isMethodAMutator($method) && !($result instanceof self)) {
				if (isset(static::$boxedType) && isType($result, static::$boxedType)) {
					return new static($result);
				}
				return new self($result);
			}

			if (!$this->isMethodTapped($method)) {
				return $result;
			}

			return $subject;
		}

		throw new \BadMethodCallException(\sprintf(
			'Method %s::%s does not exist', static::class, $method
		));
	}
}
