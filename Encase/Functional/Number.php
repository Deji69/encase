<?php
namespace Encase\Functional;

class Number extends Value
{
	protected static $boxedType = [
		'int' => 'int',
		'float' => 'float',
		'bool' => 'bool',
		'string' => 'numeric',
	];

	/**
	 * Construct a Func using a callable.
	 * This can be used to disambiguate real functions from strings and arrays.
	 *
	 * @param  int|float $value
	 */
	public function __construct($value)
	{
		assertType($value, ['int', 'bool', 'float'], 'value');
		$this->value = $value;
	}
}
