<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2010 -- Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet\Database;

/**
 * Failnet - Database class,
 * 	    Extension of PDO, adapted to suit Failnet's needs.
 *
 *
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
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
	 * @param string $table - The table that we are looking at
	 * @param string $type - The type of statement we are looking at
	 * @param string $statement - The actual PDO statement that is to be prepared (if we are preparing a statement)
	 * @return void
	 */
	public function armQuery($table, $type, $statement)
	{
		$this->statements[$table][$type] = $this->prepare($statement);
	}

	/**
	 * Prepared query object retrieval and execution
	 * @param string $table - The table that we are looking at
	 * @param string $type - The type of statement we are looking at
	 * @return PDO_Statement - An instance of PDO_Statement.
	 * @throws Failnet\Exception
	 */
	public function useQuery($table, $type)
	{
		if(!isset($this->statements[$table][$type]))
			throw new Exception(ex(Exception::ERR_INVALID_PREP_QUERY));
		return $this->statements[$table][$type];
	}
}
