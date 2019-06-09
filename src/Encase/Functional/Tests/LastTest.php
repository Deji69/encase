<?php
namespace Encase\Functional\Tests;

use Encase\Tests\TestCase;
use Encase\Functional\Collection;
use function Encase\Functional\last;

class LastTest extends TestCase
{
	/** @dataProvider casesLast */
	public function testLast($value, $expect)
	{
		$result = last($value);
		$this->assertSame($expect, $result);
	}

	public function casesLast()
	{
		return [
			'With null' => [
				'value' => null,
				'expect' => null,
			],
			'With empty array' => [
				'value' => [],
				'expect' => null,
			],
			'With array' => [
				'value' => [1, 2, 3, 4, 5],
				'expect' => 5,
			],
			'With stdObj' => [
				'value' => (object)[1, 2, 3, 4, 5],
				'expect' => 5,
			],
			'With empty string' => [
				'value' => '',
				'expect' => null,
			],
			'With string' => [
				'value' => 'foo bar',
				'expect' => 'r',
			],
			'With empty collection' => [
				'value' => Collection::make(),
				'expect' => null,
			],
			'With collection' => [
				'value' => Collection::make(1, 2, false),
				'expect' => false,
			],
			'With empty collection iterator' => [
				'value' => Collection::make()->getIterator(),
				'expect' => null,
			],
			'With collection iterator' => [
				'value' => Collection::make(1, 2, 3)->getIterator(),
				'expect' => 3,
			],
		];
	}
}
