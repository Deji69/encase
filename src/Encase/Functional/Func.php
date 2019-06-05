<?php
namespace Encase\Functional;

/**
 * @method static box(callable $value)
 */
class Func extends Value
{
	protected static $boxedType = [
		'callable' => 'callable'
	];

	/**
	 * Construct a Func using a callable.
	 * This can be used to disambiguate real functions from strings and arrays.
	 *
	 * @param  callable $function
	 */
	public function __construct(callable $function)
	{
		assertType($function, 'callable', 'function');
		$this->value = $function;
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
