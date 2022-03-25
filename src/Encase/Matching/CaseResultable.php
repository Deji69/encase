<?php
namespace Encase\Matching;

interface CaseResultable
{
	/**
	 * Get the case result.
	 *
	 * @param  array $args
	 * @return mixed
	 */
	public function getValue($args);
}
