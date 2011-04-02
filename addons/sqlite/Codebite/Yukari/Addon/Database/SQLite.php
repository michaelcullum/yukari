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

namespace Codebite\Yukari\Addon\Database;
use Codebite\Yukari\Kernel;

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
class SQLite extends \Codebite\SQLightning\PDO
{
	/**
	 * Initializes a connection to the specified database.
	 * @see PDO::__construct()
	 */
	public function connect($dsn, $username = NULL, $password = NULL, $driver_options = NULL)
	{
		$this->loadPDO(new PDO($dsn, $username, $password, $driver_options));
	}

	/**
	 * Run a specific database schema file
	 * @param string $filename - The filename to execute, will look in all already-defined autoload paths
	 * @return void
	 */
	public function runSchema($filename)
	{
		$autoloader = Kernel::getAutoloader();
		$this->db->exec(file_get_contents($autoloader->getFile($filename)));
	}

	/**
	 * Checks to see if a specified table exists.
	 * @param string $table_name - The name of the table to check.
	 * @return boolean - Whether or not the table exists
	 */
	public function tableExists($table_name)
	{
		return (bool) $this->db->query('SELECT COUNT(*) FROM sqlite_master WHERE name = ' . $this->db->quote($table_name))->fetchColumn();
	}

	/**
	 * @note: example query definition:
	 * <code>
	 *
	 * \Codebite\Yukari\Addon\Database\Sqlite->defineQuery('query_name', function(\PDO $db, $id) {
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
