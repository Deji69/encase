<?php
namespace Encase\Matching\Patterns;

use ArrayIterator;

class WildcardPattern extends Pattern
{
	public function __construct($bindName = null)
	{
		parent::__construct(null, $bindName);
	}

	public function match(ArrayIterator $argIt)
	{
		if ($this->bindName !== null) {
			return [$this->bindName => $argIt->current()];
		}
		return true;
	}
}
