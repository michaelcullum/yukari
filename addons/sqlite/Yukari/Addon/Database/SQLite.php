<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2011 -- Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Yukari\Addon\Database;
use Yukari\Kernel;

/**
 * Yukari - SQLite Database class,
 * 	    Extension of PDO, adapted to suit our needs.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class SQLite extends \PDO
{
	/**
	 * @var array - Stores all anonymous functions that we use for queries
	 */
	protected $queries = array();

	/**
	 * @ignore
	 * Just overriding the __construct() method of the parent
	 */
	public function __construct() {	}

	/**
	 * Initializes a connection to the specified database.
	 * @see PDO::__construct()
	 */
	public function connect($dsn, $username = NULL, $password = NULL, $driver_options = NULL)
	{
		parent::__construct($dsn, $username, $password, $driver_options);
	}

	/**
	 * Run a specific database schema file
	 * @param string $filename - The filename to execute, will look in all already-defined autoload paths
	 * @return void
	 */
	public function runSchema($filename)
	{
		$this->exec(file_get_contents(Kernel::get('core.autoloader')->getFile($filename)));
	}

	/**
	 * Checks to see if a specified table exists.
	 * @param string $table_name - The name of the table to check.
	 * @return boolean - Whether or not the table exists
	 */
	public function tableExists($table_name)
	{
		return (bool) $this->query('SELECT COUNT(*) FROM sqlite_master WHERE name = ' . $this->quote($table_name))->fetchColumn();
	}

	/**
	 * Define a query for later use.
	 * @param string $query_name - The name to store the query as.
	 * @param \Closure $query - An anonymous function used as the "query" to execute.
	 * @return \Yukari\Addon\Database\SQLite - Provides a fluent interface.
	 */
	public function defineQuery($query_name, \Closure $query)
	{
		$this->queries[$query_index] = $query;
		return $this;
	}

	/**
	 * Execute a stored query function and return the results
	 * @note uses func_num_args, first arg is the query index, and the rest gets passed to the function as params 2+
	 * @return mixed - The value returned from the query function
	 *
	 * @throws \InvalidArgumentException
	 */
	public function query()
	{
		$argc = func_num_args();
		if($argc < 1)
		{
			throw new \InvalidArgumentException('Required query index for \\Yukari\\Addon\\Database\\SQLite::query() not provided');
		}

		$args = func_get_args();
		list($query_index, $args) = array_pad($args, 2, array());

		if(!isset($this->queries[$query_index]))
		{
			throw new \InvalidArgumentException('The query associated with the query index specified does not exist');
		}

		$return = call_user_func_array($this->queries[$query_index], array_merge(array($this->db), (array) $args));
		return $return;
	}

	/**
	 * @note: example query definition:
	 * <code>
	 *
	 * \Yukari\Addon\Database\Sqlite->defineQuery('query_name', function(\PDO $db, $id) {
	 *	$sql = 'SELECT id, value
	 *			FROM sometable
	 *			WHERE id = :id';
	 *		$q = $db->prepare($sql);
	 *		$q->bindParam(':id', $id, PDO::PARAM_STR);
	 *		$q->execute();
	 *		$result = $q->fetch(PDO::FETCH_ASSOC);
	 *
	 *		// Avoiding PDOStatement locking issues...
	 *		$q = NULL;
	 *		return $result;
	 *	});
	 *
	 * </code>
	 */
}
