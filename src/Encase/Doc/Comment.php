<?php
namespace Encase\Doc;

use Encase\Regex\Regex;
use Encase\Functional\Str;
use Encase\Functional\Value;
use function Encase\Functional\split;

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
		$this->source = Value::make($source)->apply('trim');
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
		$comment = Str::make($comment);
		$meta = new static($comment);
		$comment = $this->source;

		if ($meta->isInline) {
			$meta->comment = $meta->source->slice(2)->apply('ltrim');
		}

		if ($meta->isDocBlock) {
			$lines = [];

			$comment = $comment->slice(2, -2)->split("\n")->map('trim');

			$comment->each(function ($line) use (&$lines) {
				if (empty($line)) {
					$lines[] = '';
					return;
				}

				if (\strpos($line, '*/') !== false) {
					return false;
				}

				if ($line[0] !== '*') {
					return;
				}

				$line = \ltrim(\substr($line, 1));
				$lines[] = $line;
			});

			return static::parseDocBlockLines($meta, $lines);
		}

		return $meta;
	}

	/**
	 * Parses the lines contained within a doc block comment.
	 *
	 * @param  \Encase\Doc\Comment  $comment
	 * @param  string[]  $lines
	 * @return \Encase\Doc\Comment
	 */
	protected static function parseDocBlockLines(Comment $comment, array $lines): Comment
	{
		for ($i = 0; $i < \count($lines); ++$i) {
			$isAttribute = \substr($lines[$i], 0, 1) === '@';
			$line = $isAttribute ? \ltrim(\substr($lines[$i], 1)) : $lines[$i];

			if (empty($line)) {
				continue;
			}

			// We merge consecutive lines into one string with newlines included unless we
			// encounter an attribute line (beginning with a @).
			// Additionally, if parsing attribute lines, we break on the first empty line.
			for ($n = $i + 1; $n < \count($lines); ++$n) {
				if (\substr($lines[$n], 0, 1) === '@') {
					break;
				}
				if (empty($lines[$n])) {
					continue;
				}

				$line .= ($line || $isAttribute ? "\n" : '').\rtrim($lines[$n]);
				$i = $n;
			}

			if ($isAttribute) {
				$comment->attributes[] = static::parseDocBlockAttribute($line);
			} else {
				// Lines not beginning with @ are part of the description.
				if ($comment->description === null) {
					$comment->description = \rtrim($line);
				} else {
					$comment->comment = \rtrim($line);
				}
			}
		}

		return $comment;
	}

	/**
	 * Parse a doc block attribute line to a CommentAttribute instance.
	 *
	 * @param  string  $line
	 * @return CommentAttribute
	 */
	protected static function parseDocBlockAttribute(string $line): CommentAttribute
	{
		$parts = \split($line, new Regex('/[^\w\-]/'), 2);
		$name = \array_shift($parts);
		$line = !empty($parts) ? \array_shift($parts) : '';
		return new CommentAttribute($name, $line);
	}
}
