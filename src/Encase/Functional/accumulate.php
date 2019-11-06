<?php
namespace Encase\Functional;

function accumulate($iterable, $initial = null, $predicate = null)
{
	if ($predicate === null) {
		$predicate = match($initial)
			(Type::numeric())(new Func(function ($current, $value) {
				return $current + $value;
			}))
			(Type::string())(new Func(function ($current, $value) {
				return $current.$value;
			}))
			(Type::array())(function ($current, $value) {
				$current[] = $value;
				return $current;
			})
			()(function ($current, $value) {
				return $value;
			});
	}

	each(
		$iterable,
		function ($value, $key, $iterable) use (&$initial, $predicate) {
			$initial = $predicate($initial, $value, $key, $iterable);
		}
	);

	return $initial;
}
