<?php
namespace Encase\Regex;

use function Encase\Functional\split;
use function Encase\Functional\slice;
use function Encase\Functional\each;

class Regex implements Patternable
{
	const MODIFIERS = [
		'i' => 'caseless',
		'm' => 'multiline',
		's' => 'dotall',
		'x' => 'extended',
		// 'e' => 'replace_eval',       // deprecated/removed
		'A' => 'anchored',
		'D' => 'dollar_endonly',
		'S' => '',
		'U' => 'ungreedy',
		'X' => 'extra',
		'J' => 'info_jchanged',
		'u' => 'utf8'
	];

	/** @var string */
	protected $pattern;

	/** @var string|null */
	protected $modifiers = null;

	/**
	 * Construct a Regex object.
	 *
	 * @param  string  $pattern
	 */
	public function __construct($pattern)
	{
		$this->pattern = $pattern instanceof self
			? $pattern->pattern
			: $pattern;
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
	 * Get the regex pattern modifiers.
	 *
	 * @return string
	 */
	public function getModifiers(): string
	{
		if (!isset($this->modifiers)) {
			$regexModifiers = $this->splitRegexModifiers();
			$this->modifiers = $regexModifiers[1];
		}

		return $this->modifiers;
	}

	/**
	 * Check if the regex pattern has a modifier.
	 *
	 * @param  string  $flag
	 * @return bool
	 * @throws \InvalidArgumentException  Thrown if a provided modifier is invalid.
	 */
	public function hasModifier(string $modifier): bool
	{
		$modifiers = $this->getModifiers();

		return each($modifier, function ($char) use ($modifiers) {
			if (!isset(self::MODIFIERS[$char])) {
				throw new \InvalidArgumentException('Invalid PCRE pattern modifier');
			}

			if (\strpos($modifiers, $char) === false) {
				return false;
			}
		}) === null;
	}

	/**
	 * Add one or more modifier flags to the regex pattern.
	 *
	 * @param  string|array  $modifier
	 * @return self  Returns a new Regex object with the added modifier.
	 * @throws \InvalidArgumentException  Thrown if a provided modifier is invalid.
	 */
	public function addModifier(string $modifier): self
	{
		if ($this->hasModifier($modifier)) {
			return clone $this;
		}

		return new self($this->pattern.$modifier);
	}

	/**
	 * Remove a modifier flag from the regex pattern.
	 *
	 * @param  string $flag
	 * @return self   Returns a new Regex object without the modifier.
	 */
	public function removeModifier(string $flag): self
	{
		[$pattern, $modifiers, $delim] = $this->splitRegexModifiers();
		$modifiers = \str_replace($flag, '', $modifiers);
		return new self($delim.$pattern.$delim.$modifiers);
	}

	public function match(string $subject, int $offset = 0): ?Occurrence
	{
		$matches = [];

		if (\preg_match($this->getPattern(), $subject, $matches, \PREG_OFFSET_CAPTURE, $offset)) {
			return Occurrence::fromResults($matches);
		}
		return null;
	}

	/**
	 * Create a Regex object.
	 *
	 * @param  string  $pattern
	 * @return self
	 */
	public static function new($pattern)
	{
		return new self($pattern);
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
	 * Determines if the string can be used as a regex pattern.
	 *
	 * The `$delimiter` parameter can be used to only validate with a specific
	 * delimiter character. If NULL, the delimiter character is determined by
	 * the first character of `$str`.
	 *
	 * Returns TRUE for e.g. with "/[A-Z]/i".
	 * Returns FALSE if either an unescaped delimiter is not found, if any
	 * characters following the end delimiter are not valid modifier
	 * characters, or if the string is empty.
	 *
	 * @param  string $str The input string.
	 * @param  string|null $delimiter If provided, only accepts this delimiter.
	 * @return bool
	 */
	public static function isRegexString(string $str, string $delimiter = null): bool
	{
		if (empty($str)) {
			return false;
		}

		if ($delimiter === null) {
			$delimiter = $str[0];
		}

		if ($endDelimiterPos = \strrpos($str, $delimiter)) {
			if (self::isCharacterEscaped($str, $endDelimiterPos, '\\')) {
				return false;
			}

			for ($i = $endDelimiterPos + 1; $i < \strlen($str); ++$i) {
				if (!isset(self::MODIFIERS[$str[$i]])) {
					return false;
				}
			}
			return true;
		}

		return false;
	}

	/**
	 * Get an array containing the regex pattern and the modifiers separately.
	 *
	 * @return array
	 */
	protected function splitRegexModifiers(): array
	{
		if (!empty($this->pattern)) {
			$this->pattern[0];

			$pos = \strrpos($this->pattern, $this->pattern[0]);

			if ($pos !== false) {
				$modifiers = slice($this->pattern, $pos + 1);
				$pattern = slice($this->pattern, 1, $pos);
				return [$pattern, $modifiers, $this->pattern[0]];
			}
		}
		return [$this->pattern, ''];
	}

	protected static function isCharacterEscaped($str, $pos, $escapeChar)
	{
		$cur = $pos;

		while ($cur > 0 && $str[$cur - 1] == $escapeChar) {
			--$cur;
		}

		$distance = $pos - $cur - 1;
		return $distance % 2 === 0;
	}
}
