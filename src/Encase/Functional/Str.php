<?php
namespace Encase\Functional;

class Str extends Value
{
	public function __construct(string $value = '')
	{
		parent::__construct($value);
	}

	public static function make($value = null)
	{
		parent::make($value ?? '');
	}
}
