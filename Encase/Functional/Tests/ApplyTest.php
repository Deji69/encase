<?php
namespace Encase\Functional\Tests;

use Encase\Functional\Func;
use function Encase\Functional\apply;

class ApplyTest extends TestCase
{
	public function testCall()
	{
		$result = apply(1, function ($value) {
			return $value + 1;
		});
		$this->assertEquals(2, $result);
	}

	public function testCallWithoutMutatingObject()
	{
		$objA = (object)['foo' => true];
		$objB = apply($objA, function ($obj) {
			$obj->foo = false;
		});

		$this->assertTrue($objA->foo);
		$this->assertNotSame($objA, $objB);
	}

	public function testCallTrimWithDefaultArguments()
	{
		$arg = ' string ';
		$result = apply($arg, 'trim');
		$this->assertSame('string', $result);
	}

	public function testCallTrimWithCustomArguments()
	{
		$arg = ' **string** ';
		$result = apply($arg, new Func('trim'), '* ');
		$this->assertSame('string', $result);
	}
}
