<?php
namespace Encase\Matching;

class CaseContinue implements CaseResultable
{
	/** @var string[] */
	protected $captures;

	/**
	 * Construct a case result from a bound argument.
	 *
	 * @param string[] $captures
	 */
	public function __construct($captures)
	{
		$this->captures = $captures;
	}

	public function getValue($args)
	{
		return $args[$this->arg];
	}
}
