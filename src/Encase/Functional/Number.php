<?php
namespace Encase\Functional;

class Number extends Value
{
	/**
	 * Construct a Func using a callable.
	 * This can be used to disambiguate real functions from strings and arrays.
	 *
	 * @param  int|float $value
	 */
	public function __construct($value)
	{
		assertType($value, 'numeric', 'value');
		$this->value = $value;
	}

	public static function cast($value)
	{
		if (\is_string($value) && \is_numeric($value)) {
			return +$value;
		}
		return $value;
	}
}
