<?php
namespace Encase\Functional\Traits;

use function Encase\Functional\each;
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
	 * Box a value into a managed wrapper.
	 *
	 * @param  mixed  $value
	 * @return self|static
	 */
	public static function box($value)
	{
		if ($value instanceof static) {
			return clone $value;
		}

		if (\is_object($value) && !$value instanceof \Generator) {
			$value = clone $value;
		}

		if (\method_exists(static::class, 'cast')) {
			$value = static::cast($value);

			if ($value instanceof self) {
				return $value;
			}
		}

		return new static($value);
	}

	/**
	 * Call a Functional function using this instance as the first argument.
	 *
	 * @param  string  $method
	 * @param  array   $args
	 * @return static|$this
	 */
	public function __call($method, $args)
	{
		return self::callFunctionalMethod($this, $method, $args);
	}

	/**
	 * Call a function like a static method and box the result.
	 *
	 * @param [type] $name
	 * @param [type] $args
	 * @return static|self|mixed
	 */
	public static function __callStatic($name, $args)
	{
		return self::callFunctionalStaticMethod($name, $args);
	}

	/**
	 * Check if the given function will mutate its subject.
	 *
	 * @param  string  $function
	 * @return bool
	 */
	private static function isFunctionAMutator(string $function): bool
	{
		return \in_array($function, static::getMethodFunctionsThatMutate());
	}

	/**
	 * Check if the given function will return its first subject argument.
	 *
	 * @param  string  $function
	 * @return bool
	 */
	private static function isFunctionTapped(string $function): bool
	{
		return \in_array($function, static::getTappedMethodFunctions());
	}

	/**
	 * Call a Functional function using $subject as the first argument.
	 * Classes using this trait can override __call and use this to carry out
	 * functions on an object the class owns.
	 *
	 * @param  mixed  $subject
	 * @param  string $method
	 * @param  array  $args
	 * @return static|self|$this
	 * @throws \BadMethodCallException If method doesn't exist.
	 */
	protected static function callFunctionalMethod(&$subject, $method, $args)
	{
		$function = static::getMethodFunction($method, true);
		$result = $function($subject, ...$args);

		if (!self::isFunctionAMutator($function) || $result instanceof self) {
			if (self::isFunctionTapped($function)) {
				return $subject;
			}
		}

		return $result;
	}

	protected static function callFunctionalStaticMethod($method, $args)
	{
		$function = static::getMethodFunction($method);
		$result = $function(...$args);

		if (\in_array($method, static::getStaticMethodsWithBoxedReturns())) {
			return static::box($result);
		}

		return $result;
	}

	/**
	 * Gets the fully-qualified function name from the method name callable via
	 * this class.
	 *
	 * @param string $method
	 * @param bool $ignoreBoxedStatics If TRUE, static methods with boxed
	 *                                 returns are ignored.
	 * @return string|null
	 * @throws \BadMethodCallException If method doesn't exist.
	 */
	private static function getMethodFunction(string $method, bool $ignoreBoxedStatics = false): ?string
	{
		$function = each(static::getMethodFunctionNamespaces(), function ($namespace) use ($method, $ignoreBoxedStatics) {
			$function = $namespace.$method;

			if (!\function_exists($function)) {
				return;
			}

			if (\in_array($function, static::getFunctionsToExcludeAsMethodCalls())) {
				return;
			}

			if ($ignoreBoxedStatics && \in_array($method, static::getStaticMethodsWithBoxedReturns())) {
				return;
			}

			return $function;
		}, true);

		if ($function === null) {
			throw new \BadMethodCallException(\sprintf(
				'Method %s::%s does not exist', static::class, $method
			));
		}

		return $function;
	}

	/**
	 * Get a list of namespaces where functions can be called as methods of
	 * this class.
	 *
	 * @return string[]
	 */
	protected static function getMethodFunctionNamespaces(): array
	{
		return [
			'Encase\\Functional\\',
		];
	}

	/**
	 * Get a list of functions which cannot be called as methods of this class
	 * even if they are in an included namespace.
	 *
	 * @return string[]
	 */
	protected static function getFunctionsToExcludeAsMethodCalls(): array
	{
		return [
			'Encase\\Functional\\assertType',
			'Encase\\Functional\\box',
		];
	}

	protected static function getMethodFunctionsThatMutate(): array
	{
		return [
			'Encase\\Functional\\apply',
			'Encase\\Functional\\concat',
			'Encase\\Functional\\map',
			'Encase\\Functional\\slice',
			'Encase\\Functional\\split',
			'Encase\\Functional\\transform',
			'Encase\\Functional\\union',
			'Encase\\Functional\\unique',
		];
	}

	protected static function getStaticMethodNames(): array
	{
		return [];
	}

	protected static function getStaticMethodsWithBoxedReturns(): array
	{
		return [];
	}

	protected static function getTappedMethodFunctions(): array
	{
		return [];
	}
}
