<?php
namespace Encase\Matching;

class CaseArg implements CaseResultable
{
	/** @var string */
	protected $arg;

	/**
	 * Construct a case result from a bound argument.
	 *
	 * @param string $arg
	 */
	public function __construct($arg)
	{
		$this->arg = $arg;
	}

	public function getValue($args)
	{
		return $args[$this->arg];
	}
}
