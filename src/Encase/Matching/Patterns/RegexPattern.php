<?php
namespace Encase\Matching\Patterns;

use Encase\Regex\Patternable as RegexPatternable;

use function Encase\Functional\accumulate;
use function Encase\Functional\isIndexedArray;

class RegexPattern extends Pattern
{
	/**
	 * Construct a Regex pattern.
	 *
	 * @param \Encase\Regex\Patternable $pattern
	 */
	public function __construct(RegexPatternable $pattern)
	{
		parent::__construct($pattern->getPattern());
	}

	public function match($argIt)
	{
		$matches = [];

		if (\preg_match($this->value, $argIt->current(), $matches)) {
			if (isIndexedArray($matches)) {
				return [$matches];
			}
			return accumulate($matches, [], function ($result, $value, $key) {
				if (\is_string($key)) {
					$result[$key] = $value;
				}
				return $result;
			});
		}
		return false;
	}
}
