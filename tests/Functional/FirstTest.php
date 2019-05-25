<?php
namespace Tests\Functional;

use Encase\Tests\TestCase;
use Encase\Functional\Collection;
use function Encase\Functional\first;

class FirstTest extends TestCase
{
	/** @dataProvider casesFirst */
	public function testFirst($value, $expect)
	{
		$result = first($value);
		$this->assertSame($expect, $result);
	}

	public function casesFirst()
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
				'expect' => 1,
			],
			'With stdObj' => [
				'value' => (object)[1, 2, 3, 4, 5],
				'expect' => 1,
			],
			'With empty string' => [
				'value' => '',
				'expect' => null,
			],
			'With string' => [
				'value' => 'foo bar',
				'expect' => 'f',
			],
			'With empty collection' => [
				'value' => Collection::make(),
				'expect' => null,
			],
			'With collection' => [
				'value' => Collection::make(false, 1, 2),
				'expect' => false,
			],
			'With empty collection iterator' => [
				'value' => Collection::make()->getIterator(),
				'expect' => null,
			],
			'With collection iterator' => [
				'value' => Collection::make(1, 2, 3)->getIterator(),
				'expect' => 1,
			],
		];
	}
}
