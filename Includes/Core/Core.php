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
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 * @deprecated  since 3.0.0 - will be removed soon
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet\Core;
use Failnet as Root;
use Failnet\Bot as Bot;
use Failnet\Lib as Lib;

/**
 * Failnet - Core class,
 *      Failnet in a nutshell.  Faster, smarter, better, and with a sexier voice.
 *
 *
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Core extends Root\Base
{
/**
 * Failnet core class properties
 */

	/**
	 * @var array - Automagical property for use with magic methods
	 */
	private $virtual_storage = array();

	/**
	 * @var array - Various config settings et al.
	 */
	public $settings = array();

	/**
	 * @var array - Config file settings
	 */
	public $config = array();

/**
 * Failnet core methods
 */
	/**
	 * Instantiates Failnet and sets everything up.
	 * @return void
	 */
	public function __construct()
	{
		// Begin loading our core objects
		$core_objects = array(
			'lang'		=> 'Failnet\\Core\\Language',
			'socket'	=> 'Failnet\\Core\\Socket',
			'db'		=> 'Failnet\\Core\\Database',
			'log'		=> 'Failnet\\Core\\Log',
			'irc'		=> 'Failnet\\Core\\IRC',
			'plugin'	=> 'Failnet\\Core\\Plugin',
			'cron'		=> 'Failnet\\Core\\Cron',
			'hash'		=> 'Failnet\\Lib\\Hash',
		);
		Bot::core('ui')->status('- Loading Failnet core objects');
		foreach($core_objects as $core_object_name => $core_object_class)
		{
			Bot::setCore($core_object_name, $core_object_class);
			Bot::core('ui')->system("--- Loaded core object $core_object_class");
		}
		unset($core_objects);

		// Setup the DB connection.
		$this->setupDB();

		// Load our node files
		Bot::core('ui')->status('- Loading Failnet node objects');
		foreach($this->config('nodes_list') as $node)
		{
			Bot::setNode($node, '\\Failnet\\Node\\' . ucfirst($node));
			Bot::core('ui')->system("--- Loaded node object $node");
		}

		$this->checkInstall();

		// Load plugins
		Bot::core('ui')->status('- Loading Failnet plugins');
		// @todo autoload plugins
		Bot::core('plugin')->pluginLoad($this->config('plugin_list'));

		// Load our config settings
		Bot::core('ui')->system('- Loading config settings');
		Bot::core('db')->useQuery('config', 'get_all')->execute();
		$result = Bot::core('db')->useQuery('config', 'get_all')->fetchAll();
		foreach($result as $row)
		{
			$this->settings[$row['name']] = $row['value'];
		}

		// This is a hack to allow us to restart Failnet if we're running the script through a batch file.
		Bot::core('ui')->system('- Removing termination indicator file');
		if(file_exists(FAILNET_ROOT . 'Data/Restart.inc'))
			unlink(FAILNET_ROOT . 'Data/Restart.inc');

		usleep(500); // In case of restart/reload, to prevent 'Nick already in use' (which asplodes everything)
		Bot::core('ui')->ready();
	}

	/**
	 * Setup the database connection and load up our prepared SQL statements
	 * @return void
	 * @throws Failnet\Exception
	 */
	public function setupDB()
	{
		// Load/setup the database
		Bot::core('ui')->system('- Connecting to the database');
		try
		{
			// Initialize the database connection
			Bot::core('db')->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			Bot::core('db')->connect('sqlite:' . FAILNET_ROOT . 'Data/DB/' . basename(md5($this->config('server') . '::' . $this->config('user'))) . '.db');


			// We want this as a transaction in case anything goes wrong.
			Bot::core('ui')->system('- Initializing the database');
			Bot::core('db')->beginTransaction();

			// Load up the list of files that we've got, and do stuff with them.
			$schemas = scandir(FAILNET_ROOT . 'Schemas');
			foreach($schemas as $schema)
			{
				if($schema[0] == '.' || substr(strrchr($schema, '.'), 1) != 'sql' || $schema == 'Schema_data.sql')
					continue;

				$tablename = substr($schema, 0, strrpos($schema, '.'));
				$results = Bot::core('db')->query('SELECT COUNT(*) FROM sqlite_master WHERE name = ' . Bot::core('db')->quote($tablename))->fetchColumn();
				if(!$results)
				{
					Bot::core('ui')->system("-  Installing the $tablename database table...");
					Bot::core('db')->exec(file_get_contents(FAILNET_ROOT . 'Schemas/' . $schema));
				}
			}

			Bot::core('ui')->system('--- Preparing database queries...');

			// Let's prepare the default prepared statements.
			// Config table
			Bot::core('db')->armQuery('config', 'create', 'INSERT INTO config ( name, value ) VALUES ( :name, :value )');
			Bot::core('db')->armQuery('config', 'get_all', 'SELECT * FROM config');
			Bot::core('db')->armQuery('config', 'get', 'SELECT * FROM config WHERE LOWER(name) = LOWER(:name) LIMIT 1');
			Bot::core('db')->armQuery('config', 'update', 'UPDATE config SET value = :value WHERE LOWER(name) = LOWER(:name)');
			Bot::core('db')->armQuery('config', 'delete', 'DELETE FROM config WHERE LOWER(name) = LOWER(:name)');

			// @todo move to authorize
			// Users table
			Bot::core('db')->armQuery('users', 'create', 'INSERT INTO users ( nick, authlevel, password ) VALUES ( :nick, :authlevel, :hash )');
			Bot::core('db')->armQuery('users', 'set_pass', 'UPDATE users SET password = :hash WHERE user_id = :user');
			Bot::core('db')->armQuery('users', 'set_level', 'UPDATE users SET authlevel = :authlevel WHERE user_id = :user');
			Bot::core('db')->armQuery('users', 'set_confirm', 'UPDATE users SET confirm_key = :key WHERE user_id = :user');
			Bot::core('db')->armQuery('users', 'get', 'SELECT * FROM users WHERE LOWER(nick) = LOWER(:nick) LIMIT 1');
			Bot::core('db')->armQuery('users', 'get_level', 'SELECT authlevel FROM users WHERE LOWER(nick) = LOWER(:nick) LIMIT 1');
			Bot::core('db')->armQuery('users', 'get_confirm', 'SELECT confirm_key FROM users WHERE user_id = :user LIMIT 1');
			Bot::core('db')->armQuery('users', 'delete', 'DELETE FROM users WHERE user_id = :user');

			// Sessions table
			Bot::core('db')->armQuery('sessions', 'create', 'INSERT INTO sessions ( key_id, user_id, login_time, hostmask ) VALUES ( :key, :user, :time, :hostmask )');
			Bot::core('db')->armQuery('sessions', 'delete_key', 'DELETE FROM sessions WHERE key_id = :key');
			Bot::core('db')->armQuery('sessions', 'delete_user', 'DELETE FROM sessions WHERE user_id = :user');
			Bot::core('db')->armQuery('sessions', 'delete_old', 'DELETE FROM sessions WHERE login_time < :time');
			Bot::core('db')->armQuery('sessions', 'delete', 'DELETE FROM sessions WHERE LOWER(hostmask) = LOWER(:hostmask)');

			// Access list table
			Bot::core('db')->armQuery('access', 'create', 'INSERT INTO access ( user_id, hostmask ) VALUES ( :user, :hostmask )');
			Bot::core('db')->armQuery('access', 'delete', 'DELETE FROM access WHERE (user_id = :user AND LOWER(hostmask) = LOWER(:hostmask) )');
			Bot::core('db')->armQuery('access', 'delete_user', 'DELETE FROM access WHERE user_id = :user');
			Bot::core('db')->armQuery('access', 'get', 'SELECT hostmask FROM access WHERE user_id = :user');

			// Commit the stuffs
			Bot::core('db')->commit();
		}
		catch (PDOException $e)
		{
			// Something went boom.  Time to panic!
			Bot::core('db')->rollBack();

			// Chain the exception
			throw new Exception(ex(Exception::ERR_PDO_EXCEPTION, $e->getMessage()), $e);
		}
	}

	// @todo document
	public function checkInstall()
	{
		// Check to see if our rand_seed exists, and if not we need to execute our schema file (as long as it exists of course). :)
		Bot::core('db')->useQuery('config', 'get')->execute(array(':name' => 'rand_seed'));
		$rand_seed_exists = Bot::core('db')->useQuery('config', 'get')->fetch(PDO::FETCH_ASSOC);
		if(!$rand_seed_exists && file_exists(FAILNET_ROOT . 'Schemas/Schema_data.sql'))
		{
			try
			{
				Bot::core('db')->beginTransaction();

				// @todo move to authorize plugin/node
				// Add the default user if Failnet was just installed
				Bot::core('db')->useQuery('users', 'create')->execute(array(':nick' => $this->config('owner'), ':authlevel' => self::AUTH_OWNER, ':hash' => Bot::core('hash')->hash($this->config('user'))));

				// Now let's add some default data to the database tables
				Bot::core('db')->exec(file_get_contents(FAILNET_ROOT . 'Schemas/Schema_data.sql'));

				Bot::core('db')->commit();
			}
			catch (PDOException $e)
			{
				// Roll back ANY CHANGES MADE, something went boom.
				Bot::core('db')->rollBack();

				// Chain the exception
				throw new Exception(ex(Exception::ERR_PDO_EXCEPTION, $e->getMessage()), $e);
			}
		}
	}

	/**
	 * Run Failnet! :D
	 * @return void
	 */
	public function run()
	{
		Bot::core('socket')->connect();
		Bot::core('plugins')->handleConnect();

		// Begin looping.
		while(true)
		{
			// Real quick, we gotta clean out the event queue just in case there's junk in there.
			Bot::core('plugins')->event_queue = array();

			// Check for tasks that need run, and take care of them.
			Bot::core('cron')->runTasks();

			// First off, fire off our tick.
			//Bot::core('plugins')->handleTick();

			// Grab our event, if we have one.
			$event = Bot::core('socket')->get();

			if($event)
				Bot::core('plugins')->handleEvent($event);

			// Do we have anything to do?
			if(!empty(Bot::core('plugins')->event_queue))
			{
				$result = Bot::core('plugins')->handleDispatch();
				if($result !== true)
					break;
			}
		}
		Bot::core('plugins')->handleDisconnect();
		Bot::core('irc')->quit($this->config('quit_msg'));
		$this->terminate($result->arguments[0]);
	}

	/**
	 * Terminates Failnet, and restarts if ordered to.
	 * @param boolean $restart - Should Failnet try to restart?
	 * @return void
	 */
	public function terminate($restart = true)
	{
		if(Bot::core('socket')->socket !== NULL)
			Bot::core('irc')->quit($this->config('quit_msg'));
		if($restart && $this->config('run_via_shell'))
		{
			// Just a hack to get it to restart through batch, and not terminate.
			file_put_contents(FAILNET_ROOT . 'Data/Restart.inc', 'yesh');
			// Dump the log cache to the file.
			// @todo recode for the new log system
			Bot::core('log')->add('--- Restarting Failnet ---', true);
			Bot::core('ui')->system('-!- Restarting Failnet');
			Bot::core('ui')->shutdown();
			exit(0);
		}
		else
		{
			// Just a hack to get it to truly terminate through batch, and not restart.
			if($this->config('run_via_shell'))
			{
				if(file_exists(FAILNET_ROOT . 'Data/Restart.inc'))
					unlink(FAILNET_ROOT . 'Data/Restart.inc');
			}
			// Dump the log cache to the file.
			// @todo recode for the new log system
			Bot::core('log')->add('--- Terminating Failnet ---', true);
			Bot::core('ui')->system('-!- Terminating Failnet');
			Bot::core('ui')->shutdown();
			exit(1);
		}
	}

	/**
	 * Magic method __get() to use for referencing specific module classes, used to return the property desired
	 * @param string $name - The name of the module class to use
	 * @return object - The object we want to use, or void.
	 * @throws Failnet\Exception
	 */
	public function __get($name)
	{
		if(!array_key_exists($name, $this->virtual_storage))
			throw new Exception(ex(Exception::ERR_INVALID_VIRTUAL_STORAGE_SLOT, $name));
		return $this->virtual_storage[$name];
	}

	/**
	 * Magic method __set() to use for referencing specific module classes, used to set a specific property
	 * @param string $name - The name of the module class to use
	 * @param mixed $value - What we want to set this to
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->virtual_storage[$name] = $value;
	}

	/**
	 * Magic method __isset() to use for referencing specific module classes, used to check if a certain property is set
	 * @param string $name - The name of the module class to use
	 * @return boolean - Whether or not the property is set.
	 */
	public function __isset($name)
	{
		 return isset($this->virtual_storage[$name]);
	}

	/**
	 * Magic method __isset() to use for referencing specific module classes, used to unset a certain property
	 * @param string $name - The name of the module class to use
	 * @return void
	 */
	public function __unset($name)
	{
		unset($this->virtual_storage[$name]);
	}
}
