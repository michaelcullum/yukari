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
 * @copyright   (c) 2009 - 2011 Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace emberlabs\materia\Metadata;
use Codebite\Yukari\Kernel;
use \OpenFlame\Dbal\Connection as DbalConnection;

/**
 * Yukari - Addon metadata object,
 *      Provides some information regarding the addon.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Whitelist extends \emberlabs\materia\Metadata\MetadataBase
{
	/**
	 * @var string - The addon's version.
	 */
	protected $version = 'core';

	/**
	 * @var string - The addon's author information.
	 */
	protected $author = 'Damian Bushong';

	/**
	 * @var string - The addon's name.
	 */
	protected $name = 'Database';

	/**
	 * @var string - The addon's description.
	 */
	protected $description = 'Provides basic database connection management.';

	/**
	 * Hooking method for addon metadata objects, called to initialize the addon after the dependency check has been passed.
	 * @return void
	 */
	public function initialize()
	{
		$options = Kernel::getConfigNamespace('db');

		if($type === NULL)
		{
			if(!isset($options['type']))
			{
				throw new \RuntimeException('No database type specified for connection');
			}
			$type = $options['type'];
		}

		$dsn = $username = $password = $db_options = NULL;
		switch($type)
		{
			case 'sqlite':
				if(!isset($options['file']))
				{
					throw new \RuntimeException('No database file specified for sqlite database connection');
				}
				$dsn = sprintf('sqlite:%s', $options['file']);
			break;

			case 'mysql':
			case 'mysqli': // in case someone doesn't know that pdo doesn't do mysqli
				if(!isset($options['host']) || !isset($options['name']) || !isset($options['username']))
				{
					throw new \RuntimeException('Missing or invalid database connection parameters, cannot connect to database');
				}
				$dsn = sprintf('mysql:host=%s;dbname=%s', ($options['host'] ?: 'localhost'), $options['name']);
				$username = $options['username'];
				$password = $options['password'] ?: '';
				$db_options = array(
					\PDO::MYSQL_ATTR_INIT_COMMAND		=> 'SET NAMES utf8',
					\PDO::MYSQL_ATTR_FOUND_ROWS		=> true,
				);
			break;

			case 'pgsql':
			case 'postgres':
			case 'postgresql':
				if(!isset($options['host']) || !isset($options['name']) || !isset($options['username']))
				{
					throw new \RuntimeException('Missing or invalid database connection parameters, cannot connect to database');
				}
				$dsn = sprintf('pgsql:host=%s;dbname=%s', ($options['host'] ?: 'localhost'), $options['name']);
				$username = $options['username'];
				$password = $options['password'] ?: '';
			break;

			default:
				throw new \RuntimeException('Invalid or unsupported database type specified for connection');
			break;
		}

		DbalConnection::getInstance()
			->connect($dsn, $username, $password, $db_options);
	}

	/**
	 * Hooking method for addon metadata objects for executing own code on pre-load dependency check.
	 * @return boolean - Does the addon pass the dependency check?
	 */
	public function checkDependencies()
	{
		return true;
	}
}
