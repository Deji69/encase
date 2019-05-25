<?php
namespace Encase\Tests;

use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	public function tearDown(): void
	{
		Mockery::close();
	}

	protected static function it(string $message, ...$args)
	{
		if (count($args) == 1) {
			self::assertTrue($args[0], $message);
		} else {
			do {
				switch ($args[1]) {
				case 'equals':
				case 'must equal':
					self::assertEquals($args[2], $args[0], $message);
					break;
				case 'not equals':
				case 'must not equal':
					self::assertNotEquals($args[2], $args[0], $message);
					break;
				case 'is':
				case 'must be':
					self::assertSame($args[2], $args[0], $message);
					break;
				case 'is not':
				case 'must not be':
					self::assertNotSame($args[2], $args[0], $message);
					break;
				}
				array_splice($args, 0, 3);
			} while (!empty($args));
		}
	}

	protected static function assert(...$args)
	{
		self::it('', ...$args);
	}
}
