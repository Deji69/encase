<?php
namespace Encase\DB\Query\Grammars;

use Encase\DB\Query\Builder;

class Grammar
{
	/**
	 * Options for compilation.
	 *
	 * @const array
	 */
	public const DEFAULT_OPTIONS = [
		'pretty' => false
	];

	/** @var array */
	protected $operators = [];

	/** @var array */
	protected $options;

	/** @var string */
	protected $tablePrefix;

	/**
	 * Get the table prefix.
	 *
	 * @return string
	 */
	public function getTablePrefix(): string
	{
		return $this->tablePrefix;
	}

	/**
	 * Set the table prefix.
	 *
	 * @param string $prefix
	 * @return void
	 */
	public function setTablePrefix(string $prefix)
	{
		$this->tablePrefix = $prefix;
	}

	public function compile(Builder $builder, array $options = []): string
	{
		$this->options = array_merge(self::DEFAULT_OPTIONS, $options);

		switch ($builder->getType()) {
			case Builder::SELECT:
				return $this->compileSelect($builder);
			case Builder::DELETE:
				return $this->compileDelete($builder);
			case Builder::INSERT:
				return $this->compileInsert($builder);
			case Builder::UPDATE:
				return $this->compileUpdate($builder);
		}

		throw new \InvalidArgumentException('Invalid query builder state');
	}

	protected function compileSelect(Builder $builder, array $options = []): string
	{
		$parts = [
			'SELECT '.$this->compileColumnList($builder),
			'FROM '.$this->compileTable($builder->from)
		];
		if (!empty($builder->wheres)) {
			$parts[] = 'WHERE '.$this->compileConditions($builder);
		}
		return implode($this->options['pretty'] ? "\n" : ' ', $parts);
	}

	protected function compileConditions(Builder $builder): string
	{
		$sql = '';

		foreach ($builder->wheres as $where) {
			$sql .= ($sql ? ' '.$where['boolean'].' ' : '')
				.$this->{"where{$where['type']}"}($builder, $where);
		}

		return $sql;
	}

	/**
	 * Compile a basic where clause.
	 *
	 * @param  \Encase\DB\Query\Builder  $builder
	 * @param  array  $where
	 * @return string
	 */
	protected function whereBasic(Builder $builder, $where): string
	{
		$value = $this->parameter($where['value']);
		return $this->wrap($where['column']).' '.$where['operator'].' '.$value;
	}

	protected function whereNull(Builder $builder, $where): string
	{
		return $this->wrap($where['column']).' IS NULL';
	}

	protected function whereNotNull(Builder $builder, $where): string
	{
		return $this->wrap($where['column']).' IS NOT NULL';
	}

	protected function whereTrue(Builder $builder, $where): string
	{
		return $this->wrap($where['column']);
	}

	protected function whereFalse(Builder $builder, $where): string
	{
		return 'NOT '.$this->wrap($where['column']);
	}

	protected function whereIn(Builder $builder, $where): string
	{
		return $this->wrap($where['column']).' IN ('.$this->parameterize($where['values']).')';
	}

	protected function whereNotIn(Builder $builder, $where): string
	{
		return $this->wrap($where['column']).' NOT IN ('.$this->parameterize($where['values']).')';
	}

	/**
	 * Compile a nested where clause.
	 *
	 * @param  \Encase\DB\Query\Builder  $query
	 * @param  array  $where
	 * @return string
	 */
	protected function whereNested(Builder $query, array $where): string
	{
		$conds = $this->compileConditions($where['query']);
		return count($where['query']->wheres) > 1 ? '('.$conds.')' : $conds;
	}

	/**
	 * Get query parameter placeholder.
	 *
	 * @param  mixed  $value
	 * @return string
	 */
	public function parameter($value): string
	{
		return '?';
	}

	/**
	 * Get query paramater placeholders for array.
	 *
	 * @param  array  $values
	 * @return string
	 */
	public function parameterize(array $values): string {
		return implode(', ', array_map([$this, 'parameter'], $values));
	}

	/**
	 * Compile a column list into an SQL friendly format.
	 *
	 * @param  \Encase\DB\Query\Builder  $builder
	 * @return string
	 */
	protected function compileColumnList(Builder $builder): string
	{
		$columns = $builder->columns;

		if (empty($columns)) {
			return '*';
		}

		$columns = array_unique($columns);
		$compiledColumns = [];

		foreach ($columns as $alias => $column) {
			$sql = $this->wrap($column);

			if (is_string($alias)) {
				$sql = $this->wrap($alias)
					.' AS '
					.$this->wrapValue($column);
			} else {
				$sql = $this->wrap($column);
			}

			$compiledColumns[] = $sql;
		}

		return implode(', ', $compiledColumns);
	}

	protected function compileTable(string $table): string
	{
		return $this->wrap($this->tablePrefix.$table, true);
	}

	/**
	 * Compile column select to SQL.
	 *
	 * @param  \Encase\DB\Query\Expression|string  $value
	 * @return string
	 */
	protected function wrap($value): string
	{
		if (is_string($value)) {
			$parts = preg_split('#\s+as\s+#i', $value, 2);

			if (count($parts) > 1) {
				return $this->wrap($parts[0])
					.' AS '.$this->wrapValue($parts[1]);
			} else {
				$value = $parts[0];
			}

			$parts = explode('.', $value);
			$parts = array_map(function ($value, $key) use ($parts) {
				return ($key == 0 && count($parts) == 2) || ($key == 1 && count($parts) >= 3)
					? $this->wrapTable($value)
					: $this->wrapValue($value);
			}, $parts, array_keys($parts));

			return implode('.', $parts);
		}

		throw new \InvalidArgumentException('Invalid argument');
	}

	protected function wrapTable(string $value): string
	{
		return $this->wrapValue($this->tablePrefix.$value);
	}

	/**
	 * Wrap a value as a string.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function wrapValue(string $value): string
	{
		return $value === '*'
			? '*'
			: '"'.str_replace('"', '""', $value).'"';
	}
}
