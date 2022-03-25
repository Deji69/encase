<?php
namespace Encase\Matching\Patterns;

class ListPattern implements Patternable
{
	/** @var array */
	protected $list;

	public function __construct($list)
	{
		$this->list = $list;
	}
}
