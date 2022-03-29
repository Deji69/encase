<?php
namespace Encase\Functional\Tests;

use Encase\Functional\Collection;
use function Encase\Functional\concat;

class ConcatTest extends TestCase
{
	public function testConcatString()
	{
		$str = 'foo';
		$result = concat($str, ' bar');
		$this->assertSame('foo', $str);
		$this->assertSame('foo bar', $result);
	}

	public function testConcatStringMultipleArgs()
	{
		$str = 'foo';
		$result = concat($str, ' bar', ' cat');
		$this->assertSame('foo bar cat', $result);
	}

	public function testConcatArray()
	{
		$array = [1, 2, 3];
		$result = concat($array, 4);
		$this->assertSame([1, 2, 3, 4], $result);
	}

	public function testConcatArrayMultipleArgs()
	{
		$array = [1, 2, 3];
		$result = concat($array, 4, 5);
		$this->assertSame([1, 2, 3, 4, 5], $result);
	}

	public function testConcatArrayObject()
	{
		$obj = new \ArrayObject(['a' => 1, 'b' => 2, 'c' => 3]);
		$expect = new \ArrayObject(['a' => 1, 'b' => 2, 'c' => 3, 4]);
		$result = concat($obj, 4);
		$this->assertNotSame($obj, $result);
		$this->assertEquals($expect, $result);
	}

	public function testConcatArrayObjectMultipleArgs()
	{
		$obj = new \ArrayObject(['a' => 1, 'b' => 2, 'c' => 3]);
		$expect = new \ArrayObject(['a' => 1, 'b' => 2, 'c' => 3, 4, 5]);
		$result = concat($obj, 4, 5);
		$this->assertNotSame($obj, $result);
		$this->assertEquals($expect, $result);
	}

	public function testConcatCollection()
	{
		$coll = new Collection(1, 2, 3);
		$expect = [1, 2, 3, 4, 5];
		$result = concat($coll, 4, 5);
		$this->assertNotSame($coll, $result);
		$this->assertEquals($expect, $result->all());
	}

	public function testConcatIterator()
	{
		$coll = new Collection(1, 2, 3);
		$expect = [1, 2, 3, 4, 5];
		$result = concat($coll->getIterator(), 4, 5);
		$this->assertNotSame($coll, $result);
		$this->assertEquals($expect, $result->getArrayCopy());
	}

	public function testConcatGenerator()
	{
		$gen = testGenerator();
		$result = concat($gen, 4, 5);
		$this->assertNotSame($gen, $result);
		$this->assertEquals([1, 2, 3, 4, 5], $result);
	}

	/** @dataProvider casesInvalidArguments */
	public function testInvalidArguments($value)
	{
		$this->expectException(\InvalidArgumentException::class);
		concat($value);
	}

	public function casesInvalidArguments()
	{
		return [
			'With null' => [null],
			'With integer' => [1],
			'With float' => [3.14],
			'With stdClass' => [(object)['a' => 1]],
		];
	}
}

function testGenerator()
{
	for ($i = 1; $i <= 3; ++$i) {
		yield $i;
	}
}
