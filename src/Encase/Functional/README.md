Encase Functional Library
=========================

- [Encase Functional Library](#encase-functional-library)
  - [Examples](#examples)
    - [Functional functions + OOP Methods](#functional-functions--oop-methods)
      - [Method chaining](#method-chaining)
      - [Non-mutability](#non-mutability)
  - [Types](#types)
    - [`BoxIterator`](#boxiterator)
    - [`Collection`](#collection)
    - [`Func`](#func)
    - [`Number`](#number)
    - [`Str`](#str)
  - [Functions](#functions)
    - [`apply`](#apply)
      - [PHP internal function difference](#php-internal-function-difference)
    - [`assertType`](#asserttype)
    - [`isType`](#istype)
      - [Example](#example)
  - [Exception Types](#exception-types)
    - [`InvalidTypeError`](#invalidtypeerror)

This library draws inspiration from others such as lodash, underscore.php and
Laravel, providing various functional programming features combined with
OOP-style interfaces for type abstractions. This allows functional functions to
be called as if they were methods of those objects, with chainability as a
sweet bonus.

There are several other features included to provide additional support for
programming functionally in PHP as comfortably as in JavaScript, while taking
advantage of PHP's features, including "boxing" so POD types can be easily and
safely wrapped up in objects and managed with greater flexibility.

The following Functional types exist:
  * `Number` for integers and floats.
  * `Str` for strings.
  * `Func` for functions (any `callable` - this helps disambiguate callable strings and arrays)
  * `Collection` for arrays.
  * `Value` for *any* type, including objects.

All non-`Value` types inherit from `Value`.

## Examples

### Functional functions + OOP Methods

All Functional functions and OOP objects are under the `Encase\Functional` namespace. Types begin with an upper-case character whereas functions begin with a lowercase, as is conventional. Any function in `Encase\Functional` can be called as a method on a `Functional` type without importing the function.

```php
use Encase\Functional\Str;
use function Encase\Functional\split;

$array = split('foo');        // returns: ['f', 'o', 'o']

$str = Str::make('foo');      // or: new Str('foo')
$array = $str->split();       // returns: new Collection(['f', 'o', 'o'])
$newStr = $str->join(',');    // returns: 'f,o,o'
```

As you may notice, the two methods had a significant difference when calling `split()`: the function call returned a plain `array` while the method proxy call returned a `Collection` instance for chainability. This is true even if you pass an object to a function - in this example, a plain `array` is returned rather than a `Collection` instance:

```php
split(Str::make('foo'));      // returns: ['f', 'o', 'o']
```

If you require minimal overhead speed you may prefer the functions and dealing with POD types over the object-oriented methods. No Functional function will ever return a Functional OOP object. However, the niceties of OOP and method chaining have their benefits for more writing more presentable and less verbose code.

#### Method chaining

While Functional object methods mostly just proxy calls to Functional functions, they also handle any necessary type conversions to and from Functional objects where appropriate to allow for intuitive chaining:

```php
Str::make('a.b.c.d')->split('.')->join(', ');   // returns: 'a, b, c, d'
```

#### Non-mutability

Functional aims to reduce mutability. This means the majority of Functional functions and methods do not mutate their subject, but rather return a new value with mutations applied.

In this example, the `map()` function is used to return a new object with elements replaced depending on the result of a function call - leaving `$array` untouched in the process:

```php
$array = new Collection('f', 'o', 'o');

// Assigns new Collection(['b', 'a', 'a']) to $newArray:
$newArray = $array->map(function ($char) {
  return $char === 'f' ? 'b' : 'a';
});

$foo = $array->join('.');      // 'f.o.o'
$baa = $newArray->join('.');   // 'b.a.a'
```

## Types

Most types provided in this library are designed to be used as objects with functional methods, similarly to the core objects in JavaScript.

### `BoxIterator`

An array iterator which boxes elements appropriately upon accessing them. For example, a string element is boxed in a `Str` instance upon accessing, and an array to a `Collection` instance.

### `Collection`

TODO: Rename to `Arr`?

Extends: `Value`  
Boxes: `array`

Similar to Laravel collections, a value wrapper which can be used to manage PHP arrays in a functional way.

### `Func`

This is merely used as a wrapper around function objects in order to disambiguate PHP callables, which can be strings and arrays which may be called as functions.

### `Number`

Extends: `Value`  
Boxes: `int`, `float`, `bool` (converts: `string`)

Similar to the Number class in JavaScript, this is a value wrapper which can be used to manage integer and float values in a functional way.

### `Str`

Extends: `Value`  
Boxes: `string` (converts: `int`, `float`, `bool`)

A value wrapper for PHP strings which can be used to manage integer and float values in a functional way.

## Functions

This is a list of the functions provided by the library. Most of these are common in many functional languages and libraries although there may be some differences and additional features.

All functions try to make maximum use of native PHP features as well as possible and aim to be flexible in their usability. One example of this is how many functions expecting `array`-like subjects will accept strings and treat them as arrays of unicode characers.

### `apply`

`apply(mixed $subject, callable $func, mixed ...$args): mixed`

Calls `$func`, passing `$subject` as the first argument (or a clone if `$subject` is an object). Optionally, more arguments (`$args`) may be passed which are also passed to `$func` after `$subject`. The result of `$func` is then returned from this function. Using this *will not mutate* `$subject`.

#### PHP internal function difference

If `$func` is *not* an instance of `Func`, then PHP Reflection is used to determine if the function is built-in and not variadic, and the number of *required* arguments. If the function is built-in and not variadic, then `$func` is called using only the number of required arguments, no matter how many are passed to `apply()`.

**Why? Why required arguments only?**

Other Functional functions, such as `map()`, use `apply()` to carry out callback operations while ensuring that `$subject` is not mutated. `map()` passes extra arguments to the callback which may be ignored by the function. So this would result in an error due to more arguments being passed than expected (and those arguments being of the wrong type for optional parameters):

```php
// Hypothetical code demonstrating what would happen if apply didn't change behaviour for internal PHP functions:
$array = ['this ', ' cannot ', ' work'];
map($array, 'trim');   // causes error: trim() expects at most 2 parameters, 3 given
```

Rather than require passing a Closure, which then calls the intended function with the right number of arguments, `apply()` will carry out the steps explained above to only pass *required* arguments, allowing PHP internals that operate on a single subject to work predictably, meaning the above code will work without any extra effort.

So, what if you want to manually pass to an optional parameter of an internal function using `apply()`? Simply wrap `'trim'` up in a `Func`:

```php
apply('***success***', new Func('trim'), '*');  // result: 'success'
```

### `assertType`

`assertType(mixed $value, string|string[] $types, string $paramName)`

Asserts that the type of `$value` is one of those given in `$types`. See [isType](#isType) for more details. This is designed to be used in cases where static type hinting falls short, such as allowing more than one possible type to be passed or allowing types or combinations of types that cannot be represented as type-hints.

If `$value` does not match any of the given types, an `Encase\Functional\Exceptions\InvalidTypeError` exception is thrown using `$types, $value, $paramName` for construction. See [InvalidTypeError](#InvalidTypeError) for more details.

### `isType`

`isType(mixed $value, string|string[] $types)`

Determines if `$value` is any one of the given `$types`. Returns a string representing the name of the type if it is, otherwise `FALSE`.

The type names can be anything useable as a static type hint in PHP, or accepted by PHP's internal `is_*` functions. Additionally, it can be `function`, which passes for a closure or `\Encase\Functional\Func` instance (this is to disambiguate from strings which may be callable).

#### Example
```php
isType(3.14, ['int', 'float']);   // returns: 'float'
isType(123, 'string');            // returns: FALSE
isType('hi', 'scalar');           // returns: 'scalar'
```

## Exception Types

Functional exceptions live in the `\Encase\Functional\Exceptions` namespace.

### `InvalidTypeError`

Extends `\InvalidArgumentException` to represent an invalid argument, but is made to be more like a `TypeError` which PHP raises with static typehints when invalid arguments are passed. The error message is more helpful, following the format:  
`Argument $arg of $func expects $type, $givenType given, called in $file on line $line`

Reflection is used to automatically determine the parameter index of `$arg` and the `$func` name, as well as the file and line it was called on.

Can be created using the static method `make`.

**Static Methods**  
`InvalidTypeError::make(string|string[] $type, mixed $value, string $paramName, int $depth = 1): InvalidTypeError`

Returns a new instance of `InvalidTypeError`, with the error message generated using the provided parameters. `$type` should be an accepted type, or array of accepted types. `$value` is used to determine the given type. `$paramName` should be the name of the variable passed to `$value` and is displayed with a `$` prepended and used to determine the argument index. `$depth` can be used to specify how many levels the errors originating file/line should be traced back to.
