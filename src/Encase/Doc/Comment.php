<?php
namespace Encase\Doc;

use Encase\Doc\Parser;
use Encase\Regex\Regex;
use Encase\Functional\Str;

class Comment
{
	/**
	 * Unaltered copy of the source comment.
	 *
	 * @var \Encase\Functional\Value containing string
	 */
	public $source;

	/**
	 * The doc block description.
	 *
	 * @var string
	 */
	public $description;

	/**
	 * An array of doc block attributes, e.g. @var, @method, etc.
	 * Keyed by the name of the attribute (e.g. 'var', 'method').
	 *
	 * @var \Encase\Doc\CommentAttribute[]
	 */
	public $attributes = [];

	/**
	 * Whether the comment is inline comment (begins with //).
	 *
	 * @var bool
	 */
	public $isInline = false;

	/**
	 * Whether this comment is a doc block (begins with /**).
	 *
	 * @var bool
	 */
	public $isDocBlock = false;

	/**
	 * Undocumented function
	 *
	 * @param string $source
	 */
	public function __construct($source)
	{
		$this->source = Str::new($source)->apply('trim');
		$this->isInline = $this->source->slice(0, 2)->is('//');
		$this->isDocBlock = $this->source->slice(0, 3)->is('/**');
	}

	/**
	 * Parse the comment string and return a Comment instance with the parsed data.
	 *
	 * @param  string  $comment
	 * @return \Encase\Doc\Comment
	 */
	public static function parse(string $comment)
	{
		return (new Parser())->parse($comment);
	}
}
