<?php
namespace Encase\Matching;

use ArrayObject;
use ReflectionFunction;
use ReflectionParameter;
use function Encase\Functional\map;
use function Encase\Functional\each;
use Encase\Matching\Patterns\Pattern;
use Encase\Matching\Exceptions\MatchException;
use Encase\Matching\Exceptions\PatternException;

class Matcher
{
	/** @var array */
	protected $cases = [];

	/** @var mixed */
	protected $hasDefault = false;

	public function __construct($cases)
	{
		if (empty($cases)) {
			throw new PatternException('Matcher must have at least one case.');
		}

		$this->cases = $cases;
	}

	/**
	 * Invoke `$this->match()` with the given arguments.
	 *
	 * @param  mixed ...$args
	 * @return mixed
	 */
	public function __invoke(...$args)
	{
		return $this->match(...$args);
	}

	/**
	 * Match the given arguments.
	 *
	 * @param  mixed ...$args
	 * @return mixed
	 * @throws \Encase\Matching\Exceptions\MatchException
	 *         Thrown if no case matched the arguments.
	 */
	public function match(...$args)
	{
		$argArray = new ArrayObject($args);

		foreach ($this->cases as $case) {
			$argIt = $argArray->getIterator();
			$captures = [];

			$result = each($case[0], function (&$pattern) use (&$argIt, &$captures, $case) {
				if ($pattern === null) {
					return;
				}

				$result = self::matchArg($pattern, $argIt);

				if (!$result) {
					return false;
				}

				if (\is_array($result)) {
					$captures = \array_merge($captures, $result);
				}

				if (!empty($case[1])) {
					if (self::checkConditions($case[1], $captures) === false) {
						return false;
					}
				}

				$argIt->next();
			});

			if ($result === false) {
				continue;
			}

			if (!\is_array($case[2])) {
				if ($case[2] instanceof CaseCall) {
					$case[2] = [
						self::getParamArgMappingForCall(
							$case[2]->getCallable(),
							$captures
						),
						$case[2]
					];
				}
			} elseif ($case[2][1] instanceof CaseCall) {
				$args = self::mapCapturesToArgs($case[2][1], $captures);
				return $case[2][1]->getValue($args);
			}

			return $case[2]->getValue($captures);
		}

		throw new MatchException('No cases matched the arguments.');
	}

	protected static function matchArg(&$patternArg, $argIt)
	{
		if ($patternArg instanceof PatternArgExact) {
			$value = $argIt->current();
			return $patternArg->arg === $value;
		}

		if ($patternArg instanceof Pattern) {
			return $patternArg->match($argIt);
		}

		// Replace the PatternArg with the built pattern in order to save time
		// should we call upon this Matcher again.
		$patternArg = PatternBuilder::build($patternArg);
		return $patternArg->match($argIt);
	}

	protected static function checkConditions(&$conditions, $captures)
	{
		return each($conditions, function (&$condition) use ($captures) {
			if (!\is_array($condition)) {
				$condition = [
					self::getParamArgMappingForCall(
						$condition,
						$captures
					),
					$condition
				];
			}

			$args = self::mapCapturesToArgs(
				$condition[0],
				$captures
			);

			if (!$condition[1](...$args)) {
				return false;
			}
		});
	}

	protected static function mapCapturesToArgs($paramArgMap, $captures)
	{
		return map($paramArgMap, function ($parameter) use ($captures) {
			return $captures[$parameter];
		});
	}

	protected static function getParamArgMappingForCall($func, $captures)
	{
		$refl = new ReflectionFunction($func);
		$i = 0;
		$params = [];
		$reflParams = $refl->getParameters();

		each(
			$reflParams,
			function ($parameter) use ($captures, &$params, &$i) {
				/** @var ReflectionParameter $parameter */
				if (isset($captures[$parameter->getName()])) {
					$params[] = $parameter->getName();
					return;
				}
				if (!isset($captures[$i])) {
					return false;
				}
				$params[] = $i++;
			}
		);
		return $params;
	}
}
