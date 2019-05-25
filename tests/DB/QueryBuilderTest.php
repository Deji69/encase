<?php
namespace Tests\Database;

use Mockery as m;
use Encase\Tests\TestCase;
use Encase\DB\Query\Builder;

class QueryBuilderTest extends TestCase
{
	/** @var \Encase\DB\Query\Builder */
	protected $builder;

	protected function setUp(): void
	{
		parent::setUp();

		$this->builder = $this->getBuilder();
	}

	public function testSelect()
	{
		$this->builder->select('*')->from('table');

		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "table"');
		self::assert($this->builder->toSql(['pretty' => true]), 'is', "SELECT *\nFROM \"table\"");
	}

	public function testSelectWithColumns()
	{
		$this->builder->select('foo', 'bar')->from('table');

		self::assert($this->builder->toSql(), 'is', 'SELECT "foo", "bar" FROM "table"');

		$this->builder->select(['cat', 'dog']);

		self::assert($this->builder->toSql(), 'is', 'SELECT "cat", "dog" FROM "table"');
	}

	public function testTableQuoteWrapping()
	{
		$this->builder->select()->from('some"table');
		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "some""table"');
	}

	public function testAlias()
	{
		$this->builder->select('foo as bar')->from('table');
		self::assert($this->builder->toSql(), 'is', 'SELECT "foo" AS "bar" FROM "table"');

		$this->builder->select(['foo' => 'bar'])->from('table');
		self::assert($this->builder->toSql(), 'is', 'SELECT "foo" AS "bar" FROM "table"');
	}

	public function testAliasWrapping()
	{
		$this->builder->select('x.y as foo.bar')->from('table');
		self::assert($this->builder->toSql(), 'is', 'SELECT "x"."y" AS "foo.bar" FROM "table"');
	}

	public function testAliasWrappingWithSpacedDatabaseName()
	{
		$this->builder->select('v w.x y.z AS foo.bar')->from('table');
		self::assert($this->builder->toSql(), 'is', 'SELECT "v w"."x y"."z" AS "foo.bar" FROM "table"');
	}

	public function testAddSelect()
	{
		$this->builder->select('foo')->addSelect('bar')->addSelect('cat', 'dog')->addSelect('sheep')->from('table');
		self::assert($this->builder->toSql(), 'is', 'SELECT "foo", "bar", "cat", "dog", "sheep" FROM "table"');
	}

	public function testSelectWithPrefix()
	{
		$this->builder->getGrammar()->setTablePrefix('prefix_');
		$this->builder->select('table.foo AS bar')->from('table');
		self::assert($this->builder->toSql(), 'is', 'SELECT "prefix_table"."foo" AS "bar" FROM "prefix_table"');

		$this->builder->select('db.table.foo AS bar')->from('table');
		self::assert($this->builder->toSql(), 'is', 'SELECT "db"."prefix_table"."foo" AS "bar" FROM "prefix_table"');
	}

	public function testTableAliasWithPrefix()
	{
		$this->builder->getGrammar()->setTablePrefix('prefix_');
		$this->builder->select()->from('foo AS bar');
		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "prefix_foo" AS "bar"');
	}

	public function testDelimitedTableWrap()
	{
		$this->builder->select()->from('foo.bar');
		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "foo"."bar"');
	}

	public function testWhere()
	{
		$this->builder->select(['id'])->from('foo')->where('id', 1);
		self::assert($this->builder->toSql(), 'is', 'SELECT "id" FROM "foo" WHERE "id" = ?');
		self::assert($this->builder->getBindings(), 'equals', [1]);
	}

	public function testWhereOmittingSelect()
	{
		$this->builder->from('foo')->where('id', 1);
		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id" = ?');
	}

	public function testOrWhere()
	{
		$this->builder->from('foo')->where('id', 1)->orWhere('id', 2);
		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id" = ? OR "id" = ?');
		self::assert($this->builder->getBindings(), 'equals', [1, 2]);
	}

	public function testAndWhere()
	{
		$this->builder->from('foo')->where('id', 1)->where('type', 'test');
		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id" = ? AND "type" = ?');
		self::assert($this->builder->getBindings(), 'equals', [1, 'test']);
	}

	public function testWhereNull()
	{
		$this->builder->from('foo')->where('id', null);
		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id" IS NULL');

		$builder = $this->getBuilder();
		$builder->from('foo')->whereNull('id');
		self::assert($builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id" IS NULL');
	}

	public function testWhereNotNull()
	{
		$this->builder->from('foo')->where('id', '!=', null);
		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id" IS NOT NULL');

		$builder = $this->getBuilder();
		$builder->from('foo')->whereNotNull('id');
		self::assert($builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id" IS NOT NULL');
	}

	public function testWhereTrue()
	{
		$this->builder->from('foo')->where('id');
		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id"');

		$builder = $this->getBuilder();
		$builder->from('foo')->whereTrue('id');
		self::assert($builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id"');
	}

	public function testWhereFalse()
	{
		$this->builder->from('foo')->whereFalse('id');
		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE NOT "id"');
	}

	public function testWhereNested()
	{
		$this->builder->from('foo')->where([
			'id' => 1,
			'type' => 'test'
		]);

		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE ("id" = ? AND "type" = ?)');
		self::assert($this->builder->getBindings(), 'is', [1, 'test']);

		$builder = $this->getBuilder()->from('foo')->where([
			'id' => 1
		]);
		self::assert($builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id" = ?');
		self::assert($builder->getBindings(), 'is', [1]);
	}

	public function testWhereAndNested()
	{
		$this->builder = $this->getBuilder();
		$this->builder->from('foo')->where(['id' => 1])->where(['type' => 'test']);
		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id" = ? AND "type" = ?');
		self::assert($this->builder->getBindings(), 'is', [1, 'test']);
	}

	public function testWhereOrNested()
	{
		$this->builder->from('foo')->where(['id' => 1])->orWhere(['id' => 2]);
		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id" = ? OR "id" = ?');
		self::assert($this->builder->getBindings(), 'is', [1, 2]);
	}

	public function testWhereIn()
	{
		$this->builder->from('foo')->whereIn('id', [1, 2, 3]);
		self::assert($this->builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id" IN (?, ?, ?)');
		self::assert($this->builder->getBindings(), 'is', [1, 2, 3]);

		$builder = $this->getBuilder();
		$builder->from('foo')->where('id', [1, 2, 3]);
		self::assert($builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id" IN (?, ?, ?)');
		self::assert($builder->getBindings(), 'is', [1, 2, 3]);

		$builder = $this->getBuilder();
		$builder->from('foo')->where([
			'id' => [1, 2, 3]
		]);
		self::assert($builder->toSql(), 'is', 'SELECT * FROM "foo" WHERE "id" IN (?, ?, ?)');
		self::assert($builder->getBindings(), 'is', [1, 2, 3]);
	}

	protected function getBuilder(): Builder
	{
		$grammar = new \Encase\DB\Query\Grammars\Grammar;
		// $processor = m::mock('Illuminate\Database\Query\Processors\Processor');
		/** @var \Encase\DB\ConnectionInterface $connection */
		$connection = m::mock('Encase\DB\ConnectionInterface');
		return new Builder($connection, $grammar);
	}
}
