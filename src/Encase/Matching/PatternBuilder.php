<?php
namespace Encase\Matching;

use Encase\Regex\Regex;
use Encase\Functional\Func;
use Mockery\Matcher\Closure;
use Encase\Matching\PatternArg;
use const Encase\Matching\Support\_;
use Encase\Matching\Patterns\Pattern;
use function Encase\Functional\typeOf;
use Encase\Matching\Patterns\Patternable;
use Encase\Matching\Patterns\ExactPattern;
use Encase\Matching\Patterns\RegexPattern;
use Encase\Matching\Patterns\CallbackPattern;
use Encase\Matching\Patterns\WildcardPattern;
use Encase\Regex\Patternable as RegexPatternable;
use Encase\Matching\Exceptions\PatternBuilderException;

class PatternBuilder
{
	public static function build(PatternArg $pattern): Pattern
	{
		$numArgs = \count($pattern->args);

		if ($numArgs === 0) {
			throw new PatternBuilderException(
				'No arguments to build pattern.'
			);
		} elseif ($numArgs === 1) {
			$arg = \reset($pattern->args);

			if ($pattern = static::buildArg($arg)) {
				return $pattern;
			}
		}

		return NullPattern::new();
	}

	public static function buildArg($arg)
	{
		switch (typeOf($arg)) {
			case 'null':
				return new ExactPattern(null);

			case 'int':
				return new ExactPattern($arg);

			case 'object':
				if ($arg instanceof Closure || $arg instanceof Func) {
					return new CallbackPattern($arg);
				}

				if ($arg instanceof Patternable) {
					return $arg;
				}

				if ($arg instanceof RegexPatternable) {
					return new RegexPattern($arg);
				}
				break;

			case 'string':
				if ($arg === _ || $arg === '_') {
					return new WildcardPattern();
				}

				if (empty($arg)) {
					return null;
				}

				if ($arg[0] === '_') {
					return new WildcardPattern(\substr($arg, 1));
				}

				if (\strlen($arg) < 2) {
					return null;
				}

				if (Regex::isRegexString($arg)) {
					return new RegexPattern(new Regex($arg));
				}

				if ($arg instanceof Regex) {
					return $arg;
				}
				return null;
		}

		if (\is_callable($arg)) {
			return new CallbackPattern($arg);
		}

		return null;
	}
}
