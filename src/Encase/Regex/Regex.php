<?php
namespace Encase\Regex;

use function Encase\Functional\split;

class Regex implements Patternable
{
	/** @var string */
	protected $pattern;

	/**
	 * Construct a Regex object.
	 *
	 * @param  string  $pattern
	 */
	public function __construct(string $pattern)
	{
		$this->pattern = $pattern;
	}

	/**
	 * Get the regex pattern.
	 *
	 * @return string
	 */
	public function getPattern(): string
	{
		return $this->pattern;
	}

	/**
	 * Split a string using a regex pattern.
	 *
	 * @param  string  $string
	 * @param  int     $limit
	 * @return array
	 */
	public static function split(string $string, string $pattern, int $limit = null): array
	{
		return split($string, new static($pattern), $limit);
	}

	/**
	 * Create a Regex object.
	 *
	 * @param  string  $pattern
	 * @return static
	 */
	public static function make(string $pattern)
	{
		return new static($pattern);
	}

	/**
	 * Get the pattern string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->pattern;
	}
}
