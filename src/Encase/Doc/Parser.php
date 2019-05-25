<?php
namespace Encase\Doc;

use Encase\Doc\Comment;
use Encase\Regex\Regex;
use Encase\Functional\Str;
use Encase\Functional\Func;
use Encase\Functional\Value;
use Encase\Doc\CommentAttribute;
use Encase\Functional\Collection;

/**
 * Contains functions for parsing a doc block comment information into a
 * \Encase\Doc\Comment object.
 */
class Parser
{
	/**
	 * Parse the comment string and return an object with the parsed information.
	 *
	 * @param  string  $comment
	 * @return \Encase\Doc\Comment
	 */
	public static function parse(string $comment): Comment
	{
		$meta = new Comment($comment);
		$comment = $meta->source;

		if ($meta->isInline) {
			$meta->description = $comment->slice(2)->apply('ltrim');
		}

		if ($meta->isDocBlock) {
			$comment = $comment->slice(2, -2)->split("\n")->map('trim');

			$lines = Collection::make();

			foreach ($comment as $line) {
				if ($line->isEmpty()) {
					$lines[] = $line;
					continue;
				}

				if ($line->find('*/') !== false) {
					break;
				}

				if (!$line[0]->is('*')) {
					continue;
				}

				$line = $line->apply(new Func('ltrim'), " \t\n\r\0\x0B*");

				if (!$lines->isEmpty() || !$line->isEmpty()) {
					$lines->push($line);
				}
			}

			if ($lines->slice(-1)->last()->isEmpty()) {
				$lines->pop();
			}

			return static::parseDocBlockLines($meta, $lines);
		}

		return $meta;
	}

	/**
	 * Parses the lines contained within a doc block comment.
	 *
	 * @param  \Encase\Doc\Comment       $comment
	 * @param  \Encase\Functional\Str[]  $lines
	 * @return \Encase\Doc\Comment
	 */
	protected static function parseDocBlockLines(Comment $comment, $lines): Comment
	{
		for ($i = 0; $i < $lines->count(); ++$i) {
			$isAttribute = $lines[$i]->first() === '@';
			$line = $isAttribute ? $lines[$i]->slice(1)->apply('ltrim') : $lines[$i];

			if ($line->isEmpty()) {
				continue;
			}

			// We merge consecutive lines into one string with newlines included unless we
			// encounter an attribute line (beginning with a @).
			// Additionally, if parsing attribute lines, we break on the first empty line.
			for ($n = $i + 1; $n < $lines->count(); ++$n) {
				if ($lines[$n]->first() === '@') {
					$i = $n - 1;
					break;
				}
				if ($lines[$n]->isEmpty()) {
					continue;
				}

				$line = $line->concat($line || $isAttribute ? "\n" : '', $lines[$n]->apply('rtrim'));
				$i = $n;
			}

			if ($isAttribute) {
				$comment->attributes[] = static::parseDocBlockAttribute($line);
			} else {
				// Lines not beginning with @ are descriptions or comments.
				if ($comment->description === null) {
					$comment->description = $line->apply('rtrim')->get();
				} else {
					$comment->comment = $line->apply('rtrim')->get();
				}
			}
		}

		return $comment;
	}

	public static function parseDocBlockAttribute(Value $line): CommentAttribute
	{
		$parts = $line->split(new Regex('/[^\w\-]/'), 2);
		$name = $parts->shift();
		$line = !$parts->isEmpty() ? $parts->shift() : '';

		$attribute = new CommentAttribute($name, $line);
		return $attribute;
	}
}
