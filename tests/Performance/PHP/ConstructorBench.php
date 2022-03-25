<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
namespace Encase\Performance\PHP;

/**
 * The aim of this benchmark is to determine the overhead of using a `::new`
 * static method which calls the `new` operator vs. using the `new` operator
 * directly vs. using `::new` and omitting a constructor.
 *
 * The easy assumption is that there will be no significant overhead at all,
 * but PHP function calls have been known to be costly and some proper
 * investigation couldn't hurt.
 *
 * Stylistically, I'd prefer to call the static method, so the hope is that
 * either there are no significant costs, or that they can be offset by not
 * using constructors.
 *
 * Result: Unfortunately both the added function call and the constructor-less
 * option both add a 50% overhead. Obviously not the end of the world for most
 * cases since the level of micro-optimisation usually isn't needed, but the
 * constructor probably cannot be made totally redundant.
 */
class ConstructorBench
{
	/**
	 * @Warmup(2)
	 * @Revs(1500)
	 * @Iterations(10)
	 * @Sleep(500000)
	 */
	public function benchNewMethod()
	{
		$result = TestClass::new('a', 'b');
	}

	/**
	 * @Warmup(2)
	 * @Revs(1500)
	 * @Iterations(10)
	 * @Sleep(500000)
	 */
	public function benchNewMethodUsingConstructor()
	{
		$result = new TestClass('a', 'b');
	}

	/**
	 * @Warmup(2)
	 * @Revs(1500)
	 * @Iterations(10)
	 * @Sleep(500000)
	 */
	public function benchNewMethodNoConstructor()
	{
		$result = TestClassWithoutConstructor::new('a', 'b');
	}

	/**
	 * @Warmup(2)
	 * @Revs(1500)
	 * @Iterations(10)
	 * @Sleep(500000)
	 */
	public function benchNewOperator()
	{
		$result = new TestClassWithOnlyConstructor('a', 'b');
	}
}

class TestClass
{
	private $memberA;
	private $memberB;

	public function __construct($a, $b)
	{
		$this->memberA = $a;
		$this->memberB = $b;
	}

	public static function new($a, $b)
	{
		return new self($a, $b);
	}
}

class TestClassWithoutConstructor
{
	private $memberA;
	private $memberB;

	public static function new($a, $b)
	{
		$obj = new self();
		$obj->memberA = $a;
		$obj->memberB = $b;
		return $obj;
	}
}

class TestClassWithOnlyConstructor
{
	private $memberA;
	private $memberB;

	public function __construct($a, $b)
	{
		$this->memberA = $a;
		$this->memberB = $b;
	}
}
