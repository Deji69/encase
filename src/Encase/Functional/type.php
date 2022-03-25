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
	const TYPES = [
		'array', 'bool', 'float', 'int', 'null', 'object', 'resource', 'string'
	];

	/** @var string|null */
	public $type;

	/** @var string|null */
	public $class = null;

	public function __construct(string $type, string $class = null)
	{
		$this->type = \in_array($type, self::TYPES, true) ? $type : null;
		$this->class = $this->type === 'object' ? $class : null;
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
	 * Determine if the two Type objects represent the same type.
	 * If the type is an object, also checks they are the same class.
	 * If either type is unknown, returns FALSE.
	 *
	 * @param  Type $type
	 * @return bool
	 */
	public function equals(Type $type)
	{
		if (!isset($this->type, $type->type)) {
			return false;
		}
		return $this->type === $type->type
		    && $this->class === $type->class;
	}

	/**
	 * Determine if `$var` is an instance of this Type.
	 *
	 * Returns TRUE if `typeOf($var)` is the same as the type this Type
	 * represents. If they are both objects, uses `instanceof` to also check if
	 * `$var` is an instance of that class (or a subclass).
	 *
	 * @param  mixed $var
	 * @return bool
	 */
	public function is($var)
	{
		if (typeOf($var) === $this->type) {
			if ($this->type === 'object' && $this->class !== null) {
				return $var instanceof $this->class;
			}
			return true;
		}
		return false;
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
	 * Convenience wrapper around `new Type((string)$type, ...$arguments)`.
	 *
	 * @param  string $type
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
	public static function __callStatic($type, $arguments): Type
	{
		return new self((string)$type, ...$arguments);
	}
}
