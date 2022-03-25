<?php
namespace Encase\Matching\Patterns;

abstract class Pattern implements Patternable
{
	/** @var mixed */
	protected $value = null;

	/** @var string|null */
	protected $bindName = null;

	/**
	 * @param string|null $bindName Variable name to capture.
	 */
	public function __construct($value = null, string $bindName = null)
	{
		$this->value = $value;
		$this->bindName = !empty($bindName) ? $bindName : null;
	}

	/**
	 * Get the binding name for the wildcard.
	 *
	 * @return string
	 */
	public function getBindName()
	{
		return $this->bindName;
	}

	/**
	 * Create a new Pattern instance.
	 *
	 * @param  mixed ...$args
	 * @return static
	 */
	public static function new(...$args)
	{
		return new static(...$args);
	}
}
