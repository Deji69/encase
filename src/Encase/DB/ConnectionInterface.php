<?php
namespace Encase\DB;

interface ConnectionInterface
{
	/**
	 * Run select statement.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return void
	 */
	public function select(string $query, array $bindings = []);

	/**
	 * Run select statement and return single result.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 */
	public function selectOne(string $query, array $bindings = []);

	/**
	 * Run insert statement and return whether the insertion took place.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return bool    Whether insertion of a row took place.
	 */
	public function insert(string $query, array $bindings = []);

	/**
	 * Run update statement and return the number of rows updated.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return int     Number of rows updated.
	 */
	public function update(string $query, array $bindings = []);

	/**
	 * Run delete statement and return the number of rows deleted.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return int     Number of rows deleted.
	 */
	public function delete(string $query, array $bindings = []);
}
