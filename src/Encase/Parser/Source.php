<?php
namespace Encase\Parser;

use function Encase\Functional\size;

class Source
{
	/** @var string */
	protected $name;

	/** @var string|null */
	protected $source = null;

	/** @var string|null */
	protected $path;

	/** @var int */
	protected $size = 0;

	/** @var array */
	protected $lines = [];

	/** @var array */
	protected $lineOffsets = [];

	/** @var array */
	protected $lineCharCounts = [];

	/**
	 * Construct a source object.
	 *
	 * @param string $source
	 * @param string $name
	 * @param string $path
	 */
	public function __construct(string $code, string $name, string $path = null)
	{
		$this->name = $name;
		$this->path = $path;
		$this->code = $code;
		$this->size = size($code);

		// Capture line offsets for fast offset<->[line, column] conversion
		$lines = \preg_split('/\n/u', $code, 10000000, \PREG_SPLIT_OFFSET_CAPTURE);
		$mbOffset = 0;

		foreach ($lines as $line) {
			$this->lines[] = $line[0];
			$this->lineOffsets[] = $line[1];
			$this->lineCharCounts[] = $mbOffset;
			$mbOffset += \mb_strwidth($line[0]) + 1;
		}
	}

	/**
	 * Get the logical name of the source.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Get the relative file path, if any.
	 *
	 * @return string|null
	 */
	public function getPath(): ?string
	{
		return $this->path;
	}

	/**
	 * Get the source code.
	 *
	 * @return string
	 */
	public function getCode(): ?string
	{
		return $this->code;
	}

	/**
	 * Get the size of the source in characters.
	 *
	 * @return int
	 */
	public function getSize(): int
	{
		return $this->size;
	}

	/**
	 * Get a range of the source code string.
	 *
	 * Range in bytes. Returns null if the range is invalid.
	 * If `$size` is not specified, code from `$from` up to the first
	 * whitespace or code end is returned.
	 *
	 * @param  int  $from
	 * @param  int  $size
	 * @return string|null
	 */
	public function getToken(int $from, int $size = null): ?string
	{
		if ($from < 0) {
			return null;
		}
		if ($size < 0) {
			return null;
		}
		if ($size === null) {
			if (\preg_match('/\s/', $this->code, $m, \PREG_OFFSET_CAPTURE, $from)) {
				$size = $m[0][1] - $from;
			}
		}
		if (($from + $size) > $this->size) {
			return null;
		}
		return \mb_substr($this->code, $from, $size);
	}

	/**
	 * Get the offset of a line.
	 *
	 * @param  int  $line
	 * @return int|null
	 */
	public function getLineOffset(int $line): ?int
	{
		return $this->lineOffsets[$line - 1] ?? null;
	}

	/**
	 * Get the line number at an offset.
	 *
	 * @param  int  $offset
	 * @return int
	 */
	public function getOffsetLine(int $offset): int
	{
		foreach ($this->lineOffsets as $index => $v) {
			if ($v > $offset) {
				return $index;
			}
		}

		return \count($this->lineOffsets);
	}

	/**
	 * Get the line column number at an offset.
	 *
	 * @param  int $offset
	 * @return int
	 */
	public function getOffsetColumn(int $offset): int
	{
		if ($line = $this->getOffsetLine($offset)) {
			$lineOffset = $this->getLineOffset($line);
			$portion = \mb_substr($this->lines[$line - 1], 0, $offset - $lineOffset);
			return \mb_strlen($portion) + 1;
		}
		return $offset + 1;
	}
}
