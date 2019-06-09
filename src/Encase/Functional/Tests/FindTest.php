<?php
namespace Encase\Functional\Tests;

use Encase\Tests\TestCase;
use function Encase\Functional\find;
use Encase\Functional\Func;

class FindTest extends TestCase
{
	public function testFindInArray()
	{
		$array = [1, 2, 3, 4, 5];
		$match = find($array, 3);
		$this->assertSame([2, 3], $match);

		$array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5];
		$match = find($array, 4);
		$this->assertSame(['d', 4], $match);
	}

	public function testFindInString()
	{
		$match = find('Hello', 'l');
		$this->assertSame([2, 'l'], $match);
	}

	public function testFindInArrayReturnsFalseIfNotFound()
	{
		$array = ['a', 'b', 'c', 'd'];
		$match = find($array, 'e');
		$this->assertFalse($match);
	}

	public function testFindInStringReturnsFalseIfNotFound()
	{
		$match = find('Hello', 'j');
		$this->assertFalse($match);
	}

	public function testFindInArrayWithOffset()
	{
		$array = [1, 2, 3, 1, 2, 3];
		$match = find($array, 1, 3);
		$this->assertSame([3, 1], $match);
	}

	public function testFindInArrayWithOffsetFromEnd()
	{
		$array = [1, 2, 3, 1, 2, 3];
		$match = find($array, 1, -4);
		$this->assertSame([3, 1], $match);
	}

	public function testFindInAssociativeArrayWithOffset()
	{
		$array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 1, 'e' => 2, 'f' => 3];
		$match = find($array, 1, 3);
		$this->assertSame(['d', 1], $match);
	}

	public function testFindInAssociativeArrayWithOffsetFromEnd()
	{
		$array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 1, 'e' => 2, 'f' => 3];
		$match = find($array, 1, -5);
		$this->assertSame(['d', 1], $match);
	}

	public function testFindInStdClass()
	{
		$object = (object)['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5];
		$match = find($object, 4);
		$this->assertSame(['d', 4], $match);
	}

	public function testFindInArrayObject()
	{
		$object = new \ArrayObject(['a' => 'first', 'b' => 'second', 'c' => 'third']);
		$match = find($object, 'second');
		$this->assertSame(['b', 'second'], $match);
	}

	public function testFindTruthy()
	{
		$array = [false, 0, '', '0', true, 0.0, '1'];
		$match = find($array);
		$this->assertSame([4, true], $match);
		$match = find($array, null, 5);
		$this->assertSame([6, '1'], $match);
	}

	public function testFindTrueExplicitly()
	{
		$array = [false, 1, '1', true];
		$match = find($array, true);
		$this->assertSame([3, true], $match);
	}

	public function testFindPredicateInArray()
	{
		$array = [1, 2, 3, 4, 5];
		$match = find($array, function ($value) {
			return $value === 3;
		});
		$this->assertSame([2, 3], $match);
	}

	public function testFindPredicateInString()
	{
		$string = 'the quick brown fox jumped over the lazy dog';
		$match = find($string, function ($value) {
			return $value === 'f';
		});
		$this->assertSame([16, 'f'], $match);
	}

	public function testFindInternalFunctionPredicateInString()
	{
		$string = 'aeIou';
		$match = find($string, new Func('ctype_upper'));
		$this->assertSame([2, 'I'], $match);
	}

	public function testFindPredicateInStringWithOffset()
	{
		$string = 'hello there';
		$match = find($string, function ($value) {
			return $value === 'e';
		}, 4);
		$this->assertSame([8, 'e'], $match);
	}

	public function testFindUnicodeCharInString()
	{
		$string = 'thé quick bröwn fox jumped över the lazy dog';
		$match = find($string, 'ö', 13);
		$this->assertSame([27, 'ö'], $match);
	}
}
