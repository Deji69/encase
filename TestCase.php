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
}
