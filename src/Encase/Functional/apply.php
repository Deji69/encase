<?php
namespace Encase\Functional;

/**
 * Invoke the `$func` function on `$subject` with the given arguments.
 *
 * Calls `$func($subject, ...$args)`, but will also clone $subject if it is an
 * object, so as to prevent $func from being able to mutate it. Returns the
 * result of the function call.
 *
 * Note that the argument list passed is limited to the number of REQUIRED
 * function parameters, so as to allow PHP internal functions to be used with
 * greater flexibility and ease. This can be overridden by wrapping the `$func`
 * argument in an `\Encase\Functional\Func`, in which case all arguments will
 * be passed.
 *
 * @param  mixed  $subject The subject of the function invokation.
 * @param  callable  $func The function to apply.
 * @param  mixed  ...$args One or more arguments to pass to the function.
 * @return mixed  The result of the function call.
 */
function apply($subject, $func, ...$args)
{
	assertType($func, 'callable', 'func');

	\array_unshift(
		$args,
		\is_object($subject) && !($subject instanceof \Generator)
			? clone $subject
			: $subject
	);

	if (!$func instanceof Func) {
		$func = Func::make($func);

		if ($func->isInternal() && !$func->isVariadic()) {
			if ($nargs = $func->getNumberOfRequiredParameters()) {
				$args = \array_slice($args, 0, $nargs);
			}
		}
	}

	return \call_user_func_array($func, $args);
}
