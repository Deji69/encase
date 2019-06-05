<?php
namespace Encase\Functional;

class Str extends Value
{
	protected static $boxedType = [
		'string' => 'string',
		'int' => 'string',
		'bool' => 'string',
		'float' => 'string',
	];

	public function __construct(string $value = '')
	{
		parent::__construct($value);
	}

	public static function make(...$value)
	{
		return parent::make(implode('', $value));
	}

	/**
	 * Box value into a string wrapper instance.
	 *
	 * @param  string  $value
	 * @return \Encase\Functional\Str
	 * @throws \Encase\Functional\Exceptions\InvalidTypeError
	 */
	public static function box($value)
	{
		return parent::box($value);
	}
}
