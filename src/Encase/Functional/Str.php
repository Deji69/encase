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

	public function __construct($value = '')
	{
		$this->value = (string)$value;
	}

	public static function new(...$value)
	{
		return parent::new(\implode('', $value));
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

	/**
	 * Generate a random string of the given length.
	 *
	 * @param  int $length Size of string to generate.
	 * @return self
	 */
	public static function random($length = 16)
	{
		$str = \random_bytes($length);
		$str = \str_replace(['/', '+', '='], '', \base64_encode($str));
		return parent::box($str);
	}
}
