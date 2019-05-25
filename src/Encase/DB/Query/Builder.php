<?php
namespace Encase\DB\Query;

use function compact;
use function is_null;
use function is_array;
use function is_string;
use function is_numeric;
use function strtolower;
use function array_merge;
use function array_values;
use function func_get_args;
use function func_num_args;
use InvalidArgumentException;
use Encase\DB\ConnectionInterface;
use Encase\DB\Query\Grammars\Grammar;

class Builder
{
	public const SELECT = 0;
	public const DELETE = 1;
	public const UPDATE = 2;
	public const INSERT = 3;

	public const OPERATORS = [
		'=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
		'LIKE', 'LIKE BINARY', 'NOT LIKE', 'ILIKE',
		'&', '|', '^', '<<', '>>',
		'RLIKE', 'REGEXP', 'NOT REGEXP',
		'~', '~*', '!~', '!~*', 'SIMILAR TO',
		'NOT SIMILAR TO', 'NOT ILIKE', '~~*', '!~~*',
	];

	/** @var \Encase\DB\ConnectionInterface */
	protected $connection;

	/** @var \Encase\DB\Query\Grammars\Grammar */
	protected $grammar;

	/** @var \Encase\DB\Query\Processors\Processor */
	protected $processor;

	/**
	 * The query type, one of: SELECT, DELETE, UPDATE, INSERT
	 *
	 * @var int
	 */
	protected $type = self::SELECT;

	/**
	 * The parameter bindings.
	 *
	 * @var mixed[]
	 */
	protected $bindings = [
		'select' => [],
		'from' => [],
		'join' => [],
		'where' => [],
		'having' => [],
		'union' => [],
	];

	/**
	 * The columns to select.
	 *
	 * @var string[]|null
	 */
	public $columns;

	/**
	 * The queried table.
	 *
	 * @var string
	 */
	public $from;

	/**
	 * The where constraints.
	 *
	 * @var array[]
	 */
	public $wheres = [];

	public function __construct(ConnectionInterface $connection,
								Grammar $grammar = null,
								Processor $processor = null)
	{
		$this->connection = $connection;
		$this->grammar = $grammar;
	}

	/**
	 * Get the connection.
	 *
	 * @return \Encase\DB\ConnectionInterface
	 */
	public function getConnection(): ConnectionInterface
	{
		return $this->connection;
	}

	/**
	 * Get the query Grammar instance.
	 *
	 * @return \Encase\DB\Query\Grammars\Grammar
	 */
	public function getGrammar(): Grammar
	{
		return $this->grammar;
	}

	/**
	 * Get the type of query being built.
	 *
	 * @return int
	 */
	public function getType(): int
	{
		return $this->type;
	}

	/**
	 * Get the parameter bindings.
	 *
	 * @return mixed[]
	 */
	public function getBindings(): array
	{
		return array_merge(...array_values($this->bindings));
	}

	/**
	 * Add the value as a parameter binding.
	 *
	 * @param  mixed   $value
	 * @param  string  $type
	 * @return $this
	 */
	public function addBinding($value, $type)
	{
		if (is_array($value)) {
			$this->bindings[$type] = array_values(array_merge($this->bindings[$type], $value));
		} else {
			$this->bindings[$type][] = $value;
		}
		return $this;
	}

	/**
	 * Set the columns to be selected.
	 *
	 * @param  string[]|string|null  $columns,...
	 * @return $this
	 */
	public function select($columns = null)
	{
		$this->columns = [];
		$this->addSelect($columns);
		$this->columns = is_array($columns) ? $columns : func_get_args();
		return $this;
	}

	/**
	 * Add columns to be selected.
	 *
	 * @param  string[]|string|null  $columns,...
	 * @return $this
	 */
	public function addSelect($columns = null)
	{
		$columns = is_array($columns) ? $columns : func_get_args();
		$this->type = self::SELECT;
		$this->columns = array_merge((array)$this->columns, $columns);
		return $this;
	}

	/**
	 * Set the table to be queried.
	 *
	 * @param  string       $table
	 * @param  string|null  $alias
	 * @return $this
	 */
	public function from(string $table, string $alias = null)
	{
		$this->from = $table;
		$this->alias = $alias;
		return $this;
	}

	/**
	 * Undocumented function
	 *
	 * @param  string|array|\Closure  $column
	 * @param  string|mixed  $operator
	 * @param  mixed   $value
	 * @param  string  $boolean
	 * @return $this
	 */
	public function where($column, $operator = null, $value = null, string $boolean = 'AND')
	{
		if (is_array($column)) {
			return $this->addWhereArray($column, is_string($operator) ? $operator : $boolean);
		}

		[$value, $operator] = $this->prepareValueAndOperator(
			$value, $operator, func_num_args() === 2
		);

		if (isset($operator) && !$this->isValidOperator($operator)) {
			[$value, $operator] = [$operator, '='];
		}

		if (func_num_args() == 1) {
			return $this->whereTrue($column, $boolean);
		} elseif (is_null($value)) {
			return $this->whereNull($column, $boolean, $operator !== '=');
		} elseif (is_array($value)) {
			return $this->whereIn($column, $value, $boolean, $operator !== '=');
		}

		$type = 'Basic';

		$this->wheres[] = compact(
			'type', 'column', 'operator', 'value', 'boolean'
		);

		$this->addBinding($value, 'where');
		return $this;
	}

	public function orWhere($column, $operator = null, $value = null)
	{
		[$value, $operator] = $this->prepareValueAndOperator(
			$value, $operator, func_num_args() === 2
		);
		return $this->where($column, $operator, $value, 'OR');
	}

	public function whereNull($column, string $boolean = 'AND', bool $not = false)
	{
		$type = $not ? 'NotNull' : 'Null';
		$this->wheres[] = compact('type', 'column', 'boolean');
		return $this;
	}

	public function whereNotNull($column, string $boolean = 'AND')
	{
		return $this->whereNull($column, $boolean, true);
	}

	public function whereTrue($column, string $boolean = 'AND', bool $not = false)
	{
		$type = $not ? 'False' : 'True';
		$this->wheres[] = compact('type', 'column', 'boolean');
		return $this;
	}

	public function whereFalse($column, string $boolean = 'AND', bool $not = false)
	{
		return $this->whereTrue($column, $boolean, true);
	}

	public function whereIn($column, $values, string $boolean = 'AND', bool $not = false)
	{
		$type = $not ? 'NotIn' : 'In';
		$this->wheres[] = compact('type', 'column', 'values', 'boolean');

		foreach ($values as $value) {
			$this->addBinding($value, 'where');
		}
	}

	/**
	 * Check if the operator is valid.
	 *
	 * @param  string  $operator
	 * @return bool
	 */
	public function isValidOperator(string $operator): bool
	{
		return in_array(strtolower($operator), self::OPERATORS, true) ||
			   in_array(strtolower($operator), $this->grammar::OPERATORS, true);
	}

	/**
	 * Check if the operator and value combination is valid.
	 *
	 * @param  mixed  $operator
	 * @param  mixed  $value
	 * @return bool
	 */
	public function isOperatorAndValueValid($operator, $value): bool
	{
		return !is_null($value) || !in_array($operator, self::OPERATORS) ||
			   in_array($operator, ['=', '<>', '!=']);
	}

	public function prepareValueAndOperator($value, $operator, bool $useDefault = false): array
	{
		if ($useDefault) {
			return [$operator, '='];
		} elseif (!$this->isOperatorAndValueValid($operator, $value)) {
			throw new InvalidArgumentException('Illegal operator and value combination.');
		}
		return [$value, $operator];
	}

	protected function addWhereArray(array $conditions, string $boolean, string $method = 'where')
	{
		return $this->whereNested(function (Builder $query) use ($conditions, $method, $boolean) {
			foreach ($conditions as $key => $value) {
				if (is_numeric($key) && is_array($value)) {
					$query->{$method}(...array_values($value));
				} else {
					$query->$method($key, '=', $value, $boolean);
				}
			}
		}, $boolean);
	}

	public function whereNested(\Closure $callback, string $boolean = 'AND')
	{
		call_user_func($callback, $query = $this->forNestedWhere());
		return $this->addNestedWhereQuery($query, $boolean);
	}

	/**
	 * Get a new instance of the query builder.
	 *
	 * @return \App\DB\Query\Builder
	 */
	public function newQuery(): self
	{
		return new static($this->connection, $this->grammar, $this->processor);
	}

	/**
	 * Create a new query instance for nested where condition.
	 *
	 * @return \Encase\DB\Query\Builder|static
	 */
	public function forNestedWhere(): self
	{
		return $this->newQuery()->from($this->from);
	}

	/**
	 * Add another query builder as a nested where to the query builder.
	 *
	 * @param  \Encase\DB\Query\Builder|static $query
	 * @param  string  $boolean
	 * @return $this
	 */
	public function addNestedWhereQuery($query, $boolean = 'and'): self
	{
		if (count($query->wheres)) {
			$type = 'Nested';

			$this->wheres[] = compact('type', 'query', 'boolean');

			$this->addBinding($query->bindings['where'], 'where');
		}
		return $this;
	}

	/**
	 * Get the generated SQL.
	 *
	 * @return string
	 */
	public function toSql(array $options = []): string
	{
		return $this->grammar->compile($this, $options);
	}

	/**
	 * Get the generated SQL.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toSql();
	}
}
