<?php
namespace Encase\Functional;

/**
 * Box a value into a fitting Functional wrapper class.
 *
 * @param  mixed  $value
 * @return Value|Str|Collection|Func
 */
function box($value = null)
{
	return Value::box($value);
}
