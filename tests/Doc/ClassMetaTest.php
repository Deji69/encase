<?php
namespace Tests\Doc;

use Encase\Doc\ClassMeta;
use Encase\Tests\TestCase;
use InvalidArgumentException;

class ClassMetaTest extends TestCase
{
	public function testConstructorThrowsInvalidArgumentExceptions()
	{
		$this->expectException(InvalidArgumentException::class);
		new ClassMeta(1);
		$this->expectException(InvalidArgumentException::class);
		new ClassMeta('qwertyuiop');
	}

	public function testCanConstructUsingClassNameOrObject()
	{
		$this->expectNotToPerformAssertions();
		$testClass = new ExperimentalClass;
		new ClassMeta(ExperimentalClass::class);
		new ClassMeta($testClass);
	}

	public function testGetClassName()
	{
		$meta = new ClassMeta(ExperimentalClass::class);
		$this->assertEquals(ExperimentalClass::class, $meta->getName());
		$this->assertEquals('ExperimentalClass', $meta->getShortName());
	}

	public function testGetClassDescription()
	{
		$meta = new ClassMeta(ExperimentalClass::class);
		$this->assertEquals('Documentation for class.', $meta->getDescription());
	}
}

/**
 * Documentation for class.
 */
class ExperimentalClass
{
	/**
	 * Documented variable.
	 *
	 * @var int
	 */
	public $publicIntVar;
}
