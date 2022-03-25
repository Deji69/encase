<?php
namespace Encase\Matching\Patterns;

use ArrayIterator;

interface Patternable
{
	/**
	 * Match arguments to the pattern.
	 *
	 * @param  \ArrayIterator $argIterator Will be incremented up to the last
	 *                        argument that matches.
	 * @return bool|array  FALSE if the pattern doesn't match, TRUE or array
	 *                     containing bindings if it does match.
	 */
	public function match(ArrayIterator $argIterator);
}
