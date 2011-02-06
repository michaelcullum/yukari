<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     core
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

namespace Yukari\Database;
use Yukari\Kernel;

/**
 * Yukari - Database class,
 * 	    Extension of PDO, adapted to suit our needs.
 *
 *
 * @category    Yukari
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class PDO extends \PDO
{
	/**
	 * @var array - Stores all prepared query objects
	 */
	private $statements = array();

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
	 * Prepared query object generation and storage
	 * @param string $table - The table that we are working with
	 * @param string $type - The "name" of the query
	 * @param string $statement - The query that is to be stored for later use
	 * @return void
	 */
	public function armQuery($table, $type, $statement)
	{
		$this->statements[$table][$type] = $statement;
	}

	/**
	 * Prepared query object retrieval and execution
	 * @param string $table - The table that we are working with
	 * @param string $type - The "name" of the query
	 * @return \PDO_Statement - An instance of \PDO_Statement.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function useQuery($table, $type)
	{
		if(!isset($this->statements[$table][$type]))
			throw new \InvalidArgumentException('The query "%s" has not been defined');
		return $this->prepare($this->statements[$table][$type]);
	}
}
