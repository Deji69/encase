<?php
namespace Encase\Functional;

/**
 * Dynamically represents a type in PHPs type system.
 *
 * @method static self array()
 * @method static self bool()
 * @method static self float()
 * @method static self int()
 * @method static self null()
 * @method static self object(string $class = null)
 * @method static self resource()
 * @method static self string()
 */
class Type
{
	const SCALAR_TYPES = [
		'bool', 'float', 'int', 'string'
	];
	const TYPES = [
		'array', 'bool', 'float', 'int', 'null', 'object', 'resource', 'string'
	];

	/** @var string|null */
	public $type;

	/** @var string|null */
	public $class = null;

	public function __construct(string $type, string $class = null)
	{
		if (\in_array($type, self::TYPES, true)) {
			$this->type = $type;
			$this->class = $this->type === 'object' ? $class : null;
		} else {
			$this->type = 'object';
			$this->class = $type;
		}

		if (!empty($this->class) && $this->class[0] === '\\') {
			$this->class = \substr($this->class, 1);
		}
	}

	/**
	 * Returns the string representation of the type.
	 * Note that objects are returned as 'object', not their class name.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->type !== null ? $this->type : 'unknown type';
	}

	/**
	 * Checks whether the Type object represents the given type/class, or that
	 * they are equivalent to another Type objects type/class.
	 *
	 * @param  Type|string $type `Type`, or string representing the type.
	 * @param  string|null $class If `$type` is `'object'`, this can set the class.
	 * @return bool
	 */
	public function equals($type, string $class = null)
	{
		if ($type instanceof $type) {
			$class = $type->class;
			$type = $type->type;
		}
		if (empty($this->type) || empty($type)) {
			return false;
		}
		return $this->type === $type
		    && $this->class === $class;
	}

	/**
	 * Check if `$var` is an instance of this Type.
	 *
	 * Returns TRUE if `typeOf($var)` is the same as the type this Type
	 * represents. If they are both objects, uses `instanceof` to also check if
	 * `$var` is an instance of that class (or a subclass).
	 *
	 * @param  mixed $var
	 * @return bool
	 */
	public function check($var)
	{
		if (typeOf($var) === $this->type) {
			if ($this->type === 'object' && $this->class !== null) {
				return $var instanceof $this->class;
			}
			return true;
		}
		return false;
	}

	public static function annotate($var): string
	{
		$type = typeOf($var);

		if (\in_array($type, self::SCALAR_TYPES)) {
			if ($type === 'string') {
				$var = '\''.\addcslashes($var, '\'').'\'';
			}
			return "$type($var)";
		} elseif ($type === 'object') {
			return "$type(".\get_class($var).")";
		} elseif ($type === 'array') {
			if (\count($var) > 6) {
				$parts = \array_slice($var, 0, 3, true);
				$parts = \array_merge($parts, ['...'], \array_slice($var, -3, null, true));
			} else {
				$parts = $var;
			}

			$isSeq = isSequentialArray($parts);
			$n = 0;

			foreach ($parts as $key => &$part) {
				$partType = typeOf($part);
				$v = \in_array($partType, self::SCALAR_TYPES)
					? (string)$part
					: self::annotate($part);

				if (++$n === 4 && $part === '...') {
				} elseif ($partType === 'string') {
					$v = '\''.\addcslashes($v, '\'').'\'';
				}

				if (\is_string($key)) {
					$key = "'$key'";
				}

				$part = $isSeq ? $v : "$key => $v";
			}
			return '['.\implode(', ', $parts).']';
		}
		return "$type->type()";
	}

	/**
	 * Create a Type representing the given type/class.
	 *
	 * @param  string|null $type The type to represent.
	 * @param  string|null $class The class to represent.
	 * @return self
	 */
	public static function new($type, $class = null)
	{
		return new self($type, $class);
	}

	/**
	 * Create a Type representing the type/class of `$var`.
	 *
	 * @param  mixed $var
	 * @return self
	 */
	public static function of($var)
	{
		$type = typeOf($var);
		return new self($type, $type === 'object' ? \get_class($var) : null);
	}

	/**
	 * Create a new type using the name of the called static method.
	 *
	 * Convenience wrapper around `new Type((string)$typeName, ...$arguments)`.
	 *
	 * @param  string $typeName
	 * @param  array $arguments [string $class]
	 * @return self
	 *
	 * @example To create a Type representing a string.
	 * ```php
	 * Type::string()
	 * ```
	 *
	 * @example To create a Type representing an object of a given class.
	 * ```php
	 * Type::object('My\OwnClass')
	 * ```
	 */
	public static function __callStatic($typeName, $arguments): Type
	{
		return new self((string)$typeName, ...$arguments);
	}
}
