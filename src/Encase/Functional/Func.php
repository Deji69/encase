<?php
namespace Encase\Functional;

class Func extends Value
{
	/**
	 * Construct a Func using a callable.
	 * This can be used to disambiguate real functions from strings and arrays.
	 *
	 * @param  callable $function
	 */
	public function __construct(callable $function)
	{
		$this->value = $function;
	}
}
