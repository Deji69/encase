<?php
namespace Tests\Doc;

use Encase\Tests\TestCase;
use Encase\Doc\Parser;

/**
 * hello world
 * @something
 *
 * this is something
 *
 * @something else
 *
 * What now eh?
 */
class ParserTest extends TestCase
{
	const FullDocComment = '
		/**
		 * hello world
		 * @something
		 *
		 * This is something.
		 *
		 * @something foo
		 *
		 * bar
		 */';

	public function testCanDetectInlineComments()
	{
		$parser = $this->getParser();
		$meta = $parser->parse('// inline comment');
		$this->assertTrue($meta->isInline);
		$meta = $parser->parse('/* not an inline comment */');
		$this->assertFalse($meta->isInline);
	}

	public function testCanDetectDocBlockComments()
	{
		$meta = $this->getParser()->parse('/** doc block */');
		$this->assertTrue($meta->isDocBlock);
	}

	/**
	 * @dataProvider descriptionComments
	 */
	public function testGetDescriptionFromDocBlockComment(string $comment)
	{
		$meta = $this->getParser()->parse($comment);
		$this->assertEquals('hello world', $meta->description);
	}

	public function testGetAttributeFromDocBlockComment()
	{
		$meta = $this->getParser()->parse(self::FullDocComment);
		$this->assertEquals('something', $meta->attributes[0]->name);
		$this->assertEquals('This is something.', $meta->attributes[0]->value);
		$this->assertEquals('something', $meta->attributes[1]->name);
		$this->assertEquals("foo\nbar", $meta->attributes[1]->value);
	}

	public function getParser(): Parser
	{
		return new Parser;
	}

	public function descriptionComments()
	{
		return [
			[self::FullDocComment],
			['/** hello world */'],
			['/**
			  * hello world
			  */'],
		];
	}
}
