<?php
namespace Encase\Functional;

class Str extends Value
{
	public function __construct(string $value = '')
	{
		$this->value = $value;
	}

	/**
	 * Cast value for boxing to Str.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function cast($value)
	{
		return (string)$value;
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
		return new static($str);
	}
}
