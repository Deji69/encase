<?php
namespace Encase\Functional\Traits;

/**
 * Proxies method calls to \Encase\Functional functions.
 *
 * @method $this|static each(callable $function)
 * @method static|$this apply(callable $function)
 * @method static|$this map(callable $function, bool $preserveKeys = false)
 * @method static|$this slice(?int $begin, int $end = null)
 * @method array|false  find(mixed $predOrValue, int $offset)
 * @method int    count()
 * @method bool   isType(string|array $type)
 * @method mixed  pop()
 * @method mixed  shift()
 * @method int    size()
 * @method array  split(string $separator = '', int $limit = null)
 * @method string type()
 */
trait Functional
{
	/**
	 * Functions that shouldn't be callable as instance methods.
	 *
	 * @var array
	 */
	private static $excludeFunctions = [
		'assertType'
	];

	/**
	 * Functions that return mutated versions of their input.
	 *
	 * @var array
	 */
	private static $mutatingFunctions = [
		'map', 'slice', 'split', 'transform',
		'apply', 'concat',
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
	 * @return static|$this
	 */
	protected function callFunctionalMethod(&$subject, $method, $parameters)
	{
		$function = 'Encase\\Functional\\'.$method;

		if (!\in_array($method, static::$excludeFunctions) && \function_exists($function)) {
			$result = $function($subject, ...$parameters);

			if ($this->isMethodAMutator($method) && !($result instanceof self)) {
				return new static($result);
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
