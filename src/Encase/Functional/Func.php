<?php
namespace Encase\Functional;

use ReflectionFunctionAbstract;

/**
 * @method static box(callable $value)
 */
class Func extends Value
{
	protected static $boxedType = [
		'callable' => 'callable'
	];

	/** @var bool */
	protected $isMethod = false;

	/** @var \ReflectionFunctionAbstract */
	protected $reflection = null;

	/**
	 * Construct a Func using a callable.
	 * This can be used to disambiguate real functions from strings and arrays.
	 *
	 * @param  callable $function
	 */
	public function __construct(callable $function)
	{
		$this->value = $function;
		$this->isMethod = \is_array($function);
	}

	/**
	 * Check if the function is a closure.
	 *
	 * @return bool
	 */
	public function isClosure(): bool
	{
		return $this->value instanceof \Closure;
	}

	/**
	 * Check if the function is a method.
	 *
	 * @return bool
	 */
	public function isMethod(): bool
	{
		return $this->isMethod;
	}

	/**
	 * Check if the function is a PHP internal function.
	 *
	 * @return bool
	 */
	public function isInternal(): bool
	{
		return $this->getReflection()->isInternal();
	}

	/**
	 * Check if the function has a variable number of parameters.
	 *
	 * @return bool
	 */
	public function isVariadic(): bool
	{
		return $this->getReflection()->isVariadic();
	}

	/**
	 * Get the number of parameters.
	 *
	 * @return int
	 */
	public function getNumberOfParameters(): int
	{
		return $this->getReflection()->getNumberOfParameters();
	}

	/**
	 * Get the number of required parameters.
	 *
	 * @return int
	 */
	public function getNumberOfRequiredParameters(): int
	{
		return $this->getReflection()->getNumberOfRequiredParameters();
	}

	/**
	 * Get a ReflectionMethod or ReflectionFunction instance for the function.
	 *
	 * @return \ReflectionFunctionAbstract
	 */
	public function getReflection(): ReflectionFunctionAbstract
	{
		if (!isset($this->reflection)) {
			$this->reflection = $this->isMethod ?
				new \ReflectionMethod($this->value[0], $this->value[1]) :
				new \ReflectionFunction($this->value);
		}
		return $this->reflection;
	}

	/**
	 * Box value into a Func instance.
	 *
	 * @param  callable  $value
	 * @return \Encase\Functional\Func
	 * @throws \Encase\Functional\Exceptions\InvalidTypeError
	 */
	public static function box($value)
	{
		return parent::box($value);
	}
}
