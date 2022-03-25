<?php
namespace Encase\Parser;

class Token
{
	/** @var string */
	protected $type;

	/** @var string|null */
	protected $value;

	/** @var int|null */
	protected $offset;

	/**
	 * @param  int    $type
	 * @param  mixed  $value
	 * @param  int    $offset
	 */
	public function __construct(int $type, string $value = null, int $offset = null)
	{
		$this->type = $type;
		$this->value = $value;
		$this->offset = $offset;
	}

	/**
	 * Get the type/matched rule for the token.
	 *
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}
}
