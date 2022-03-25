<?php
namespace Encase\Matching;

use Encase\Matching\CaseResultable;

class CaseCall implements CaseResultable
{
	protected $callable;

	/**
	 * Construct a case call object.
	 *
	 * @param callable $callable
	 */
	public function __construct($callable)
	{
		$this->callable = $callable;
	}

	/**
	 * Get the callable object.
	 *
	 * @return callable
	 */
	public function getCallable()
	{
		return $this->callable;
	}

	/**
	 * Get the value by calling the callable.
	 *
	 * @param  array $args
	 * @return mixed
	 */
	public function getValue($args)
	{
		return ($this->callable)(...$args);
	}
}
