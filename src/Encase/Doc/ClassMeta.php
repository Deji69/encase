<?php
namespace Encase\Doc;

use ReflectionClass;
use Encase\Doc\Comment;
use ReflectionException;
use function func_get_args;
use InvalidArgumentException;

class ClassMeta
{
	/** @var \ReflectionClass */
	protected $reflection;

	/** @var PropertyMeta[] */
	protected $properties = [];

	/** @var \Encase\Doc\Comment */
	protected $comment;

	/**
	 * Construct a class doc parser.
	 *
	 * @param  \ReflectionClass|...mixed  $reflectionClass  An instance of \ReflectionClass or arguments which may construct one.
	 */
	public function __construct($reflectionClass)
	{
		try {
			$this->reflection = $reflectionClass instanceof ReflectionClass
				? $reflectionClass
				: new ReflectionClass(...func_get_args());
		} catch (ReflectionException $e) {
			throw new InvalidArgumentException('Must pass an instance of ReflectionClass or an argument list for its construction.');
		}
	}

	/**
	 * Get the name of the class.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->reflection->getName();
	}

	/**
	 * Get the unqualified name of the class.
	 *
	 * @return string
	 */
	public function getShortName(): string
	{
		return $this->reflection->getShortName();
	}

	/**
	 * Get the description (the first comment) of the class.
	 *
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->getDoc()->description;
	}

	/**
	 * Get parsed Doc comment metadata.
	 *
	 * @param  Parser|null  $parser
	 * @return \Encase\Doc\Comment
	 */
	public function getDoc(Parser $parser = null): Comment
	{
		if (!isset($this->comment)) {
			$parser = $parser ?? new Parser;
			$this->comment = $parser->parse($this->reflection->getDocComment());
		}
		return $this->comment;
	}
}
