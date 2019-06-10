Encase Functional Library
=========================

[![Build Status](https://travis-ci.org/Deji69/encase.svg?branch=master)](https://travis-ci.org/Deji69/encase)
[![Latest Stable Version](https://poser.pugx.org/encase/functional/v/stable)](https://packagist.org/packages/encase/functional)
[![Total Downloads](https://poser.pugx.org/encase/functional/downloads)](https://packagist.org/packages/encase/functional)
[![License](https://poser.pugx.org/encase/functional/license)](https://packagist.org/packages/encase/functional)

- [Encase Functional Library](#encase-functional-library)
- [Overview](#overview)
- [Installation](#installation)
- [Examples](#examples)
  - [Functional functions & OOP Methods](#functional-functions--oop-methods)
    - [Method chaining](#method-chaining)
    - [Non-mutability](#non-mutability)
- [String Treatment](#string-treatment)
  - [Encoding](#encoding)
- [Boxing](#boxing)
- [Types](#types)
  - [`BoxIterator`](#boxiterator)
    - [Example](#example)
  - [`Collection`](#collection)
  - [`Func`](#func)
  - [`InvalidTypeError`](#invalidtypeerror)
  - [`Number`](#number)
  - [`Str`](#str)
  - [`Value`](#value)
    - [Methods](#methods)
- [Functions](#functions)
  - [`apply`](#apply)
    - [Behavioural difference with PHP internal functions](#behavioural-difference-with-php-internal-functions)
  - [`assertType`](#asserttype)
  - [`box`](#box)
  - [`concat`](#concat)
  - [`each`](#each)
  - [`find`](#find)
  - [`first`](#first)
  - [`isType`](#istype)
    - [Types checked](#types-checked)
  - [`join`](#join)
  - [`last`](#last)
  - [`map`](#map)
  - [`pop`](#pop)
  - [`shift`](#shift)
  - [`size`](#size)
  - [`slice`](#slice)
  - [`split`](#split)
  - [`type`](#type)
  - [`values`](#values)

# Overview

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
  * `Collection` for arrays.
  * `Func` for functions (any `callable` - this helps disambiguate callable strings and arrays)
  * `Number` for integers and floats.
  * `Str` for strings.
  * `Value` for *any* type, including objects.

All non-`Value` types inherit from `Value`.

# Installation

Install using composer:
```
composer require encase/functional
```

# Examples

## Functional functions & OOP Methods

All Functional functions and OOP objects are under the `Encase\Functional` namespace. Types begin with an upper-case character whereas functions begin with a lowercase, as is conventional. Any function in `Encase\Functional` can be called as a method on a `Functional` type without importing the function.

```php
use Encase\Functional\Str;
use function Encase\Functional\split;

$array = split('foo');        // returns: ['f', 'o', 'o']

$str = Str::make('foo');      // or: new Str('foo')
$array = $str->split();       // returns: new Collection(['f', 'o', 'o'])
$newStr = $str->join(',');    // returns: new Str('f,o,o')
```

As you may notice, the two methods had a significant difference when calling `split()`: the function call returned a plain `array` while the method proxy call returned a `Collection` instance for chainability. This is true even if you pass an object to a function - in this example, a plain `array` is returned rather than a `Collection` instance:

```php
split(Str::make('foo'));      // returns: ['f', 'o', 'o']
```

If you require minimal overhead speed you may prefer the functions and dealing with POD types over the object-oriented methods. No Functional function will ever return a Functional OOP object. However, the niceties of OOP and method chaining have their benefits for more writing more presentable and less verbose code.

### Method chaining

While Functional object methods mostly just proxy calls to Functional functions, they also handle any necessary type conversions to and from Functional objects where appropriate to allow for intuitive chaining:

```php
Str::make('a.b.c.d')->split('.')->join(',');   // returns: new Str('a,b,c,d')
```

### Non-mutability

Functional aims to reduce mutability. This means the majority of Functional functions and methods do not mutate their subject, but rather return a new value with mutations applied.

In this example, the `map()` function is used to return a new object with elements replaced depending on the result of a function call - leaving `$array` untouched in the process:

```php
$array = new Collection('f', 'o', 'o');

// Assigns new Collection(['b', 'a', 'a']) to $newArray:
$newArray = $array->map(function ($char) {
  return $char === 'f' ? 'b' : 'a';
});

$foo = $array->join('.');      // returns: new Str('f.o.o')
$baa = $newArray->join('.');   // returns: new Str('b.a.a')
```

# String Treatment

Functions in this library like to treat strings as arrays of unicode characters. Thus, many functions you may know from other languages to only work on arrays will work on strings just as well. For example, a `substr`/`substring` function is not necessary, as `slice` works just as well on strings as arrays.

```php
// forget that slice and split exist for a second...
$string = Str::make('he✔ll✖o');
$array = [];

$string->each(function ($char) {
  if ($char === '✔') return;
  $array[] = $char;
  if ($char === '✖') return false;
});

// $array === ['h' ,'e', 'l', 'l', '✖']
```

## Encoding

UTF-8 is the most supported encoding for strings. However, as most Functional functions make use of PHP's `mb_*` functions, the encoding used for many is based on your PHP configuration. There is no way to override the encoding on a per-function basis.

# Boxing

This library provide support for [boxing](https://en.wikipedia.org/wiki/Object_type_(object-oriented_programming)#Boxing) various PHP types into fitting Functional object types. For example, a string can be easily boxed into a `Encase\Functional\Str` object without even specifying the type using the `box` static method of the `Encase\Functional\Value` class:

```php
$str = Value::box('hello');   // returns: new Str('hello')
$str = box('hello');          // convenience function
$str->split();                // etc...
```

This is an example of implicit boxing. Values can also be boxed explicitly, which allows for conversion from a given type:

```php
$str = Str::box(123);         // returns: new Str('123')
$value = $str->get();         // returns: '123'
```

See the information in the sub-sections of [Types](#types) for documentation of which Functional types box which native types and the conversions that are accepted.

The `box` method automatically prevents double-boxing, so you will never end up with box-ception.

# Types

Most types provided in this library are designed to be used as objects with functional methods, similarly to the core objects in JavaScript.

## `BoxIterator`

An array iterator which boxes elements appropriately upon accessing them. For example, a string element is boxed into a `Str` instance upon accessing, and an array to a `Collection` instance:

### Example

```php
$iterator = new BoxIterator(['--hello--', '--world--']);

foreach ($iterator as $str) {
  echo $str->apply(new Func('trim'), '-'), ' ';
}

// Output: hello world 
```

## `Collection`

Extends: `Value`  
Boxes: `array`

Similar to Laravel collections, a value wrapper which can be used to manage PHP arrays in a functional way.

If a `string` is passed to its constructor, the string is split up into an array of unicode characters.

## `Func`

Extends: `Value`  
Boxes: `callable`

This is merely used as a wrapper around function objects in order to disambiguate PHP callables, which can be strings and arrays which may be called as functions.

It's worth noting that since `Func` implements the `__invoke` magic method, it is considered `callable` by PHP, thus it passes type hints.

**Methods**  
`Func` provides an interface to features provided by PHP's Reflection classes. The first call to any method requiring Reflection will instantiate a new `ReflectionMethod` or `ReflectionFunction` object appropriately and store it internally for future calls. This object can be retrieved with the `getReflection` method.

  * `isClosure(): bool` - Check if the function is a closure.
  * `isMethod(): bool` - Check if the function is a method.
  * `isInternal(): bool` - Check if the function is a PHP internal function.
  * `isVariadic(): bool` - Check if the function is variadic.
  * `getNumberOfParameters(): int` - Get the number of parameters.
  * `getNumberOfRequiredParameters(): int` - Get the number of required parameters.
  * `getReflection(): ReflectionFunctionAbstract` - Get the reflection object.

## `InvalidTypeError`

Extends: `InvalidArgumentException`

Represents an invalid argument, but is made to be more like the `TypeError` which PHP raises with static typehints when invalid arguments are passed. The error message is more helpful, following the format:  
`Argument $arg of $func expects $type, $givenType given, called in $file on line $line`

Reflection is used to automatically determine the parameter index of `$arg` and the `$func` name, as well as the file and line it was called on.

Can be created using the static method `make`.

**Static Methods**  
`InvalidTypeError::make(string|string[] $type, mixed $value, string $paramName, int $depth = 1): InvalidTypeError`

Returns a new instance of `InvalidTypeError`, with the error message generated using the provided parameters. `$type` should be an accepted type, or array of accepted types. `$value` is used to determine the given type. `$paramName` should be the name of the variable passed to `$value` and is displayed with a `$` prepended and used to determine the argument index. `$depth` can be used to specify how many levels the errors originating file/line should be traced back to.

## `Number`

Extends: `Value`  
Boxes: `int`, `float`, `bool` (converts: `string`)

Similar to the Number class in JavaScript, this is a value wrapper which can be used to manage integer and float values in a functional way.

## `Str`

Extends: `Value`  
Boxes: `string` (converts: `int`, `float`, `bool`)

A value wrapper for PHP strings which can be used to manage integer and float values in a functional way.

## `Value`

Implements: `ArrayAccess`, `Countable`, `IteratorAggregate`, `JsonSerializable`  
Boxes: `object`

A value wrapper for any type which can be used to handle any type of value in a functional way. This is the basis for all other value wrappers, which are mostly designed to refine the behaviour of this class, do type assertions and allow for better type identification. This class uses the `Encase\Functional\Functional` trait to allow proxying of method calls to Functional function calls.

As well as the interface classes this class implements, it provides the magic methods for converting the contained value to a string and invoking the contained value as a function. Obviously, the contained value must actually support these operations itself.

### Methods

  * `boxIt(): BoxIterator` - convenient shorthand alias for `getBoxIterator()`
  * `count(): int` - calls [size]() for the contained value.
  * `equals($value): bool` - check if the contained value is loosely equal to `$value`
  * `get($key = null, $default = null)` - get the contained value (`$key` and `$default` are ignored for this class, but may be used by child classes)
  * `getBoxIterator(): BoxIterator` - get a `BoxIterator` for the contained value. This is only valid for `iterable` types.
  * `getIterator(): \Iterator` - get an `\Iterator` for the contained value.
  * `is($value): bool` - check if the contained value is strictly equal to `$value`
  * `isEmpty(): bool` - check if the contained value is `empty()`
  * `isNull(): bool` - check if the contained value `=== null`
  * `make(...$value): Value` - create an instance of `Value` (or child class) - if another `Value` instance is passed and is the only argument, a clone of it is returned - all provided arguments are passed on to the constructor

# Functions

This is a list of the functions provided by the library. Most of these are common in many functional languages and libraries although there may be some differences and additional features. Unless otherwise stated, none of these functions modify any arguments passed to them.

All functions try to make maximum use of native PHP features as well as possible and aim to be flexible in their usability. One example of this is how many functions expecting `array`-like subjects will accept strings and treat them as arrays of unicode characers.

**REMEMBER:** Most of these functions can be called as methods on any `Value`-derived type (or user class using the `Encase\Functional\Functional` trait). When called as methods, the contained value is always passed to the function as the first argument.

## `apply`
`apply(mixed $subject, callable $func, mixed ...$args): mixed`

Calls `$func`, passing `$subject` as the first argument (or a clone if `$subject` is an object). Optionally, more arguments (`$args`) may be passed which are also passed to `$func` after `$subject`. The result of `$func` is then returned from this function. Using this *will not mutate* `$subject`.

### Behavioural difference with PHP internal functions

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

## `assertType`
`assertType(mixed $value, string|string[] $types, string $paramName)`

Asserts that the type of `$value` is one of those given in `$types`. See [isType](#isType) for more details. This is designed to be used in cases where static type hinting falls short, such as allowing more than one possible type to be passed or allowing types or combinations of types that cannot be represented as type-hints.

If `$value` does not match any of the given types, an `Encase\Functional\Exceptions\InvalidTypeError` exception is thrown using `$types, $value, $paramName` for construction. See [InvalidTypeError](#InvalidTypeError) for more details.

*This function cannot be called as a method.*

## `box`
`box(mixed $value): Collection|Func|Number|Str|Value`

Alias for `Value::box`, which takes the `$value` and wraps it in a fitting Functional object instance. See [Boxing](#boxing) for details on this concept.

*This function cannot be called as a method. There would be no point, since this is just an alias for a static `Value` method. You can however pass the `Value` object to it in order to promote a `Value` to something more fitting.*

## `concat`
`concat(iterable|string $container, mixed ...$values): iterable|string`

Concat appends a value onto `$container`, such as a `string` or `array` (or other `iterable`). Supports multiple arguments, each of which are concatenated in succession.

**Example**
```php
concat('hello', ' world');      // returns: 'hello world'
concat([1, 2], 3, 4, 5);        // returns: [1, 2, 3, 4, 5]
$str = box('Functional');
$array = [' is', ' neat'];
$str->concat(...$array);
$str->get();                    // returns: 'Functional is neat'
```

## `each`
`each(iterable|stdClass|string|null $iterable, callable $func): mixed`

Iterates over an `iterable`, `string` (see [String Treatment](#string-treatment)) or `stdClass`, and calls the provided function for each element, passing the value as the first argument, the index/key as the second argument, and the subject as the third argument.

Supports early exit by returning *any* non-`null` value. Returns `null`, or value returned by `$func` in the case of early exit.

*Note: `$subject` may be modified if `$func` takes a reference and modifies it.*

**Example with associative array**
```php
$array = [
    'apples' => 'green',
    'tomatoes' => 'red',
    'lemons' => 'yellow'
];

each($array, function ($value, $key) {
    echo $key, ' are ', $value, "\n";
});

/* Output:
  apples are green
  tomatoes are red
  lemons are yellow
*/
```

**Example with string**
```php
$str = 'find ✔ the ✔ ticks';
$result = [];

each($str, function ($value, $index, $str) use (&$result) {
    if ($value === '✔') {
        $result[$index] = \mb_substr($str, $index);
    }
});

// $result === ['✔ the ✔ ticks', '✔ ticks']
```

**Example with early exit**
```php
$array = '✔✔✔✖✔';

$result = each($array, $index, function ($value) {
  if ($value === '✖') {
    return 'error: '.$index;
  }
  return;
});

// $result === 'error: 3'
```

## `find`
`find(array|iterable|stdClass|string $value, mixed|\Closure|\Encase\Functional\Func $pred = null, int $offset = 0): array|bool`

Searches forward through the container for a given value or predicate match. Returns an array like `[$foundIndex, $foundValue]` where `$foundIndex` is the index/key where the match was found, and `$foundValue` is the actual value that was found. If `$pred` is a function (see [isType](#istype)), it is called with each value and index/key and matches when it returns true. If `$pred` is `null`, then the first truthy value is matched. Otherwise, `$pred` is treated as a value and the following predicate is used:

```php
function ($value) use ($pred) {
  return $value === $pred;
};
```

`$offset` can be used to begin the search at a certain index. If `$offset` is negative, the search begins that far from the end of the container. While this works fine with non-sequentially indexed arrays and associative arrays, it is always treated as an index rather than a key.

**Example with array & value**
```php
$array = ['a' => false, 'b' => 1, 'c' => '1', 'd' => true, 'e' => false];
$match = find($array, true);  // returns: ['d', true]
```

**Example with string & PHP internal function**
```php
$string = 'ábcdefg';
$match = find($string, function ($value, $index) {
  return $value === 'e' && $index === 4;
});  // returns: [4, 'e']
```

**Example with unicode string & predicate**
```php
$string = 'ábcdefg';
$match = find($string, function ($value, $index) {
  return $value === 'e' && $index === 4;
});  // returns: [4, 'e']
```

**Example with PHP internal function as predicate**  
Remember to use `Func` to wrap string or array values, otherwise they will be treated as values to find rather than predicate functions. Only the value will be passed to PHP internal functions, meaning various PHP functions can be used with little extra effort:
```php
$string = 'ABCdEFG';
find($string, new Func('ctype_lower'));   // returns: [3, 'd']
find($string, 'ctype_lower');             // returns: FALSE
```

## `first`
`first(\Traversable|iterable|string|stdClass|null $iterable): mixed|null`

Gets the value of the first element in `$iterable`. Returns `null` if `$iterable` is empty.

```php
$string = first('§abc');    // returns: §
$int = first([1, 2, 3]);    // returns: 1
```

## `isType`
`isType(mixed $value, string|string[] $types): string|FALSE`

Determines if `$value` is any one of the given `$types`. Returns a string representing the name of the first type that qualifies the `$value`, otherwise `FALSE`.

The type names can be anything usable as a static type hint in PHP, those accepted by PHP's internal `is_*` functions or a class name which is checked with `instanceof`. Additionally, it can be `function`, which passes for a closure or `Encase\Functional\Func` instance (this is to allow disambiguation from strings and arrays which may be callable).

**Example**
```php
isType(3.14, ['int', 'float']);           // returns: 'float'
isType(123, 'string');                    // returns: FALSE
isType('hi', 'scalar');                   // returns: 'scalar'
isType('print', ['callable', 'string']);  // returns: 'callable'
isType('print', ['function', 'string']);  // returns: 'string'
isType(new Func('print'), 'function');    // returns: 'function'
$str = new Str('print');
$str->isType(['callable']);               // returns: 'callable'
```

### Types checked
  * **array** - `is_array()`
  * **bool** - `is_bool()`
  * **callable** - `is_callable()`
  * **countable** - `is_array()` or `instanceof \Countable` (`is_countable()` polyfilled for PHP <7.3)
  * **double**/**float**/**real** - `is_float()`
  * **function** - `instanceof \Closure` or `instanceof \Encase\Functional\Func`"
  * **int**/**integer**/**long** - `is_int()`
  * **null** - `is_null()`
  * **numeric** - `is_numeric()`
  * **object** - `is_object()`
  * **resource** - `is_resource()`
  * **scalar** - `is_scalar()`
  * **string** - `is_string()`

Any other value is treated as a class name and checked using the `instanceof` operator.

## `join`
`join(iterable|stdClass|array $iterable, ?string $separator = ',', string $lastSeparator = null): string`

Joins all values in the `$iterable` into one string, separated by `$separator`. If `$lastSeparator` is provided, the last two elements are separated by that rather than `$separator` - if there are only two elements, only `$lastSeparator` is used. If `null` is specified for `$separator`, it defaults back to `','`.

```php
$array = ['you', 'me', 'them'];
join($array);                   // returns: 'you,me,them'
join($array, ', ', ' and ');    // returns: 'you, me and them'
```

## `last`
`last(\Traversable|iterable|string|stdClass|null $iterable): mixed|null`

Gets the value of the last element in `$iterable`. Returns `null` if `$iterable` is empty.

```php
$collection = Collection::make(1, 2, 3);
$object = (object)['a' => 1, 'b' => 2, 'c' => 3];
last($collection);                    // returns: 3
last($collection->getIterator());     // returns: 3
last($object);                        // returns: 3
last('ábc§');                         // returns: '§'
```

## `map`
`map($iterable, callable|null $func = null, bool $preserveKeys = false)`

Copies `$iterable`, replacing each element with the value returned by `$func`. `$func` is called via [apply](#apply), which has some convenient exceptions for working with PHP internal functions, so see that function for details on the invokation. Aside from those exceptions, `$func` is called on each iteration with: the element value, the element index/key and the `$iterable` itself. By default, the resulting array is re-indexed - use `$preserveKeys` in order to have the resulting array use the same keys as the input.

**Examples**

Replace element values with their keys, resetting keys (essentially `array_keys`).
```php
$values = ['a' => 1, 'b' => 2, 'c' => 3];

$result = map($values, function ($value, $key) {
    return $key;
});

// $result === ['a', 'b', 'c']
```

Multiply each element in the array by two, while preserving keys.
```php
$values = [1 => 1, 2 => 2, 4 => 4, 8 => 8];

$result = map($values, function ($value, $key) {
    return $value * 2;
}, true);

// $result === [1 => 2, 2 => 4, 4 => 8, 8 => 16]
```

Trim whitespace from a whole array of strings.
```php
$values = [' trim ', ' these ', ' strings'];
map($values, 'trim');     // returns: ['trim', 'these', 'strings']
```

## `pop`
`pop(array|string|\ArrayAccess|\Traversable|\stdClass &$arrayish): mixed`

*This function mutates its input.*

Removes the last element from the `$arrayish` container and returns it. This function takes the input by reference and changes its length. Treats a string as an array of unicode characters.

**Examples**

Pop from array.
```php
$array = [1, 2, 3];
pop($array);          // returns: 3
// $array === [1, 2]
```

Pop from string.
```php
$string = '✔✔✖';
pop($string);         // returns: ✖
// $string === '✔✔'
```

## `shift`
`shift(array|string|\ArrayAccess|\Traversable|\stdClass &$arrayish): mixed`

*This function mutates its input.*

Removes the first element from the `$arrayish` container and returns it. This function takes the input by reference and changes its length. Treats a string as an array of unicode characters.

**Examples**

Shift from array.
```php
$array = [1, 2, 3];
shift($array);        // returns: 1
// $array === [1 => 2, 2 => 3]
```

Shift from string.
```php
$string = '✔✖✖';
shift($string);       // returns: ✔
// $string === '✖✖'
```

## `size`
`size(iterable|string $value): int`

Alias: `count`

Get the size of `$value`. For `iterable`, the size will be the number of elements. For `string`, the size will be the number of unicode characters. Returns 0 if `$value` is not an `iterable` or `string`.

## `slice`
`slice(iterable|\Traversable|string $value, ?int $start, int $end = null): iterable|\Traversable|string`

Extract a portion of an `array`, `string` or `\Traversable` object. Sliced traversables are returned as arrays. Returns the portion found starting from `$start` up to (but not including) `$end`.

*Note: Unlike PHP's internal functions, this uses start and end indices for determining the range, rather than a start index and a size.*

**Examples**

Get a substring.
```php
slice('Hello world', 0, 5);       // returns: 'Hello'
slice('Hello world', null, -6);   // returns: 'Hello'
slice('Hello world', 6);          // returns: 'world'
slice('Hello world', -5);         // returns: 'world'
```

Get an array slice.
```php
$array = [
    'one' => 1,
    'two' => 2,
    'three' => 3,
    'four' => 4,
    'five' => 5,
];
$array = slice($array, 2, -1);
// $array === ['three' => 3, 'four' => 4]
```

## `split`
`split(string $string, string|\Encase\Regex\Regex $separator = '', int $limit = null): array`

Splits `$string` up into an array of strings using `$separator` to separate them.

If `$separator` is an `\Encase\Regex\Regex` object from the Encase Regex library, then the contained regular expression is used to match the separator.

**Examples**

Splitting by comma.
```php
split('1,2,3,4', ',');      // returns: ['1', '2', '3', '4']
split('1,2,3,4', ',', 3);   // returns: ['1', '2', '3,4']
```

Splitting by regex.
```php
use \Encase\Regex\Regex;

$array = split('hel.lo|wor/ld', Regex::make('/[^\w]/'));

// $array === ['hel', 'lo', 'wor', 'ld']
```

## `type`
`type(mixed $value): string`

Gets the type of the variable based on which of PHP's is_* checks returns true (rather than using `gettype`). Returns `'function'` for closure objects but does not work with other callables as those are strings and arrays first.

Possible return values: array, bool, int, float, function, null, object, resource, string

## `values`
`values(\Traversable|iterable|stdClass|null $iterable): string`

Re-index the traversable, array or object. Equivalent to calling `map($iterable)` or `\array_values($iterable)` on arrays.

**Examples**
```php
$array = ['a' => 'apple', 'b' => 'ball', 'c' => 'cat'];
values($array);     // returns: ['apple', 'ball', 'cat']
```