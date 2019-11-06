<?php
namespace Encase\Functional;

class Type
{
	const string = '';

	/** @var string|null */
	public $type;

	/** @var string|null */
	public $class = null;

	public function __construct(string $type, string $class = null)
	{
		$this->type = $type;
		$this->class = $class;
	}

	public function __toString()
	{
		return $this->type !== null ? $this->type : 'unknown type';
	}

	public static function of($value)
	{
		$type = typeOf($value);
		return new self($type, $type === 'object' ? \get_class($value) : null);
	}

	public static function __callStatic($type, $arguments): Type
	{
		return new Type((string)$type, ...$arguments);
	}
}
