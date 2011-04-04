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

namespace Codebite\Yukari\Addon\Metadata;
use Codebite\Yukari\Kernel;

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
class Postgres extends \Codebite\Yukari\Addon\Metadata\MetadataBase
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
	protected $name = 'Postgres';

	/**
	 * @var string - The addon's description.
	 */
	protected $description = 'Provides access to an PostGreSQL database object.';

	/**
	 * Hooking method for addon metadata objects, called to initialize the addon after the dependency check has been passed.
	 * @return void
	 */
	public function initialize()
	{
		if(!Kernel::getConfig('db.postgres.port'))
		{
			Kernel::setConfig('db.postgres.port', 5432);
		}

		$db = Kernel::set('addon.database', new \Codebite\Yukari\Addon\Database\PostGreSQL());
		$dsn = sprintf('pgsql:host=%1$s;port=%2$s;dbname=%3$s', Kernel::getConfig('db.postgres.host'), Kernel::getConfig('db.postgres.port'), Kernel::getConfig('db.postgres.dbname'));
		$db->connect($dsn, Kernel::getConfig('db.postgres.user'), Kernel::getConfig('db.postgres.password'));
	}

	/**
	 * Hooking method for addon metadata objects for executing own code on pre-load dependency check.
	 * @return boolean - Does the addon pass the dependency check?
	 *
	 * @throws \RuntimeException
	 */
	public function checkDependencies()
	{
		if(!extension_loaded('PDO'))
		{
			throw new \RuntimeException('PHP extension "PDO" not loaded');
		}

		if(!extension_loaded('pdo_pgsql'))
		{
			throw new \RuntimeException('PHP extension "pdo_pgsql" not loaded');
		}

		// Required configs for DB connection
		$required_configs = array(
			'db.postgres.host',
			'db.postgres.dbname',
			'db.postgres.user',
			'db.postgres.password',
		);

		foreach($required_configs as $config)
		{
			if(Kernel::getConfig($config) === NULL)
			{
				throw new \RuntimeException(sprintf('Required config "%1$s" not set'));
			}
		}

		return true;
	}
}
