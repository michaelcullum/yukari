<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version		2.1.0 DEV
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @copyright	(c) 2009 - 2010 -- Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-2.0.php>.
 *
 */


/**
 * Failnet - Database class,
 * 		Extension of PDO, adapted to suit Failnet's needs.
 *
 *
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class failnet_database extends PDO
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
	 * @throws failnet_exception
	 */
	public function useQuery($table, $type)
	{
		if(!isset($this->statements[$table][$type]))
			throw new failnet_exception(failnet_exception::ERR_INVALID_PREP_QUERY);
		return $this->statements[$table][$type];
	}
}
