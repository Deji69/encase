<?php
namespace Encase\Functional;

function not(callable $predicate): callable
{
	return function () use ($predicate) {
		return !$predicate(...\func_get_args());
	};
}
