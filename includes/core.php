<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 1
 * Copyright:	(c) 2009 - Failnet Project
 * License:		GNU General Public License - Version 2
 *
 *===================================================================
 *
 */

/**
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
 */


/**
 * Failnet - Core class,
 * 		Failnet 2.0 in a nutshell.  Faster, smarter, better, and with a sexier voice.
 *
 *
 * @package core
 * @author Obsidian
 * @copyright (c) 2009 - Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_core
{
/**
 * Failnet core class properties
 */

	/**
	 * @var array - Loaded class nodes
	 */
	private $nodes = array();

	/**
	 * @var array - Loaded Failnet plugins
	 */
	private $plugins = array();

	/**
	 * @var array - List of loaded plugins
	 */
	public $plugins_loaded = array();



	/**
	 * @var integer - The UNIX timestamp for when Failnet was started
	 */
	public $start = 0;

	/**
	 * @var string - Our current usernick for the bot
	 */
	public $nick = '';

	/**
	 * @var boolean - Should we be in debug mode?
	 */
	public $debug = false;

	/**
	 * @var boolean - Should we be in silent mode?
	 */
	public $speak = true;

	/**
	 * @var array - Various config settings et al.
	 */
	public $settings = array();

	/**
	 * @var array - Config file settings
	 */
	public $config = array();

	/**
	 * @var array - What channels are we in, and what users are in them?
	 */
	public $chans = array();

    /**
     * @var array - Array of plugins and what commands they contain
     */
    public $p_commands = array();

    /**
     * @var array - Array of help entries for individual commands
     */
    public $p_help = array();

	/**
	 * @var array - Prepared PDO statements for use throughout Failnet
	 */
	public $statements = array();

	/**
	 * @var boolean - DO NOT _EVER_ CHANGE THIS, FOR THE SAKE OF HUMANITY.  {@link http://xkcd.com/534/ }
	 */
	private $can_become_skynet = FALSE;

/**
 * Failnet core constants
 */
	const HR = '---------------------------------------------------------------------';
	const ERROR_LOG = 'error';
	const USER_LOG = 'user';
	const FOUNDER = 32;
	const ADMIN = 16;
	const OP = 8;
	const HALFOP = 4;
	const VOICE = 2;
	const REGULAR = 1;

/**
 * Failnet core methods
 */
	/**
	 * Instantiates Failnet and sets everything up.
	 * @return void
	 */
	public function __construct()
	{
		// Check to make sure the CLI SAPI is being used...
		if(strtolower(PHP_SAPI) != 'cli')
			throw_fatal('Failnet must be run in the CLI SAPI');

		// Make sure that PDO and the SQLite PDO extensions are loaded, we need them.
		if(!extension_loaded('PDO'))
			throw_fatal('Failnet requires the PDO PHP extension to be loaded');
    	if(!extension_loaded('pdo_sqlite'))
			throw_fatal('Failnet requires the PDO_SQLite PHP extension to be loaded');

		// Check to see if date.timezone is empty in the PHP.ini; if so, set the timezone to prevent strict errors.
		if(!ini_get('date.timezone'))
			@date_default_timezone_set('UTC');

		// Make sure our database directory actually exists and is manipulatable
		if(!file_exists(FAILNET_DB_ROOT) || !is_readable(FAILNET_DB_ROOT) || !is_writeable(FAILNET_DB_ROOT) || !is_dir(FAILNET_DB_ROOT))
			throw_fatal('Failnet requires the database directory to exist and be readable/writeable');
		/**
         *
         * Commented because we don't really need it
		if(!file_exists(FAILNET_ROOT . 'data/weather/') || !is_readable(FAILNET_ROOT . 'data/weather/') || !is_writeable(FAILNET_ROOT . 'data/weather/') || !is_dir(FAILNET_ROOT . 'data/weather/'))
			throw_fatal('Failnet requires the weather cache directory to exist and be readable/writeable');
		 */

		// Set the time that Failnet was started.
		$this->start = time();

		// Begin printing info to the terminal window with some general information about Failnet.
		display(array(
			self::HR,
			'Failnet -- PHP-based IRC Bot version ' . FAILNET_VERSION,
			'Copyright: (c) 2009 - Obsidian',
			'License: GNU General Public License - Version 2',
			self::HR,
			'Failnet is starting up. Go get yourself a coffee.',
			self::HR,
		));

		// Load the config file
		$cfg_file = ($_SERVER['argc'] > 1) ? $_SERVER['argv'][1] : 'config';
		display("- Loading configuration file '$cfg_file' for specified IRC server");
		$this->load($cfg_file);

		// Load/setup the database
		display('- Connecting to the database');
		try
		{
			// Initialize the database connection
			$this->db = new PDO('sqlite:' . FAILNET_DB_ROOT . basename(md5($this->get('server') . '::' . $this->get('user'))) . '.db');
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// We want this as a transaction in case anything goes wrong.
			$this->db->beginTransaction();

			display('- Initializing the database');

			// Load up the list of files that we've got, and do stuff with them.
			$schemas = scandir(FAILNET_ROOT . 'includes/schemas');
			foreach($schemas as $schema)
			{
				if(substr($schema, 0, 1) == '.' || substr(strrchr($schema, '.'), 1) != 'sql' || $schema == 'schema_data.sql')
					continue;

				$tablename = substr($schema, 0, strrpos($schema, '.'));
				$results = $this->db->query('SELECT COUNT(*) FROM sqlite_master WHERE name = ' . $this->db->quote($tablename))->fetchColumn();
				if(!$results)
				{
					display(' -  Installing the ' . $tablename . ' database table...');
					$this->db->exec(file_get_contents(FAILNET_ROOT . 'includes/schemas/' . $schema));
				}
			}

			display('- Preparing database queries...');

			// Now, we need to build our default statements.
			// Config table
			$this->sql('config', 'create', 'INSERT INTO config ( name, value ) VALUES ( :name, :value )');
			$this->sql('config', 'get_all', 'SELECT * FROM config');
			$this->sql('config', 'get', 'SELECT * FROM config WHERE LOWER(name) = LOWER(:name) LIMIT 1');
			$this->sql('config', 'update', 'UPDATE config SET value = :value WHERE LOWER(name) = LOWER(:name)');
			$this->sql('config', 'delete', 'DELETE FROM config WHERE LOWER(name) = LOWER(:name)');

			// Users table
			$this->sql('users', 'create', 'INSERT INTO users ( nick, authlevel, password ) VALUES ( :nick, :authlevel, :hash )');
			$this->sql('users', 'set_pass', 'UPDATE users SET password = :hash WHERE user_id = :user');
			$this->sql('users', 'set_level', 'UPDATE users SET authlevel = :authlevel WHERE user_id = :user');
			$this->sql('users', 'set_confirm', 'UPDATE users SET confirm_key = :key WHERE user_id = :user');
			$this->sql('users', 'get', 'SELECT * FROM users WHERE LOWER(nick) = LOWER(:nick) LIMIT 1');
			$this->sql('users', 'get_level', 'SELECT authlevel FROM users WHERE LOWER(nick) = LOWER(:nick) LIMIT 1');
			$this->sql('users', 'get_confirm', 'SELECT confirm_key FROM users WHERE user_id = :user LIMIT 1');
			$this->sql('users', 'delete', 'DELETE FROM users WHERE user_id = :user');

			// Sessions table
			$this->sql('sessions', 'create', 'INSERT INTO sessions ( key_id, user_id, login_time, hostmask ) VALUES ( :key, :user, :time, :hostmask )');
			$this->sql('sessions', 'delete_key', 'DELETE FROM sessions WHERE key_id = :key');
			$this->sql('sessions', 'delete_user', 'DELETE FROM sessions WHERE user_id = :user');
			$this->sql('sessions', 'delete_old', 'DELETE FROM sessions WHERE login_time < :time');
			$this->sql('sessions', 'delete', 'DELETE FROM sessions WHERE LOWER(hostmask) = LOWER(:hostmask)');

			// Access list table
			$this->sql('access', 'create', 'INSERT INTO access ( user_id, hostmask ) VALUES ( :user, :hostmask )');
			$this->sql('access', 'delete', 'DELETE FROM access WHERE (user_id = :user AND LOWER(hostmask) = LOWER(:hostmask) )');
			$this->sql('access', 'delete_user', 'DELETE FROM access WHERE user_id = :user');
			$this->sql('access', 'get', 'SELECT hostmask FROM access WHERE user_id = :user');

			// Commit the stuffs
			$this->db->commit();
		}
		catch (PDOException $e)
		{
			// Something went boom.  Time to panic!
			$this->db->rollBack();
			throw_fatal($e);
		}

		// Load required classes and systems
		display('- Loading Failnet nodes');
		foreach($this->get('nodes_list') as $node)
		{
			$name = 'failnet_' . $node;
			$this->$node = new $name($this);
			display('=-= Loaded ' . $node . ' node');
		}

		// Set the error handler
		display('=== Setting main error handler');
		@set_error_handler(array(&$this->error, 'fail'));

		// If Failnet was just installed, we need to do something now that the auth class is loaded
		$this->sql('config', 'get')->execute(array(':name' => 'rand_seed')); //check for rand_seed existing
		$rand_seed_exists = $this->sql('config', 'get')->fetch(PDO::FETCH_ASSOC);
		if(!$rand_seed_exists)
		{
			try
			{
				$this->db->beginTransaction();

				// Add the default user if Failnet was just installed
				$this->sql('users', 'create')->execute(array(':nick' => $this->get('owner'), ':authlevel' => 100, ':hash' => $this->hash->hash($this->get('user'))));

				// Now let's add some default data to the database tables
				$this->db->exec(file_get_contents(FAILNET_ROOT . 'includes/schemas/schema_data.sql'));

				$this->db->commit();
			}
			catch (PDOException $e)
			{
				// Roll back ANY CHANGES MADE, something went boom.
				$this->db->rollBack();
				throw_fatal($e);
			}
		}

		// Load plugins
		display('- Loading Failnet plugins');
		$plugins = $this->get('plugin_list');
		$this->plugin('load', $plugins);

		// Load our config settings
		display('- Loading config settings');
		$this->sql('config', 'get_all')->execute();
		$result = $this->sql('config', 'get_all')->fetchAll();
		foreach($result as $row)
		{
			$this->settings[$row['name']] = $row['value'];
		}

		// This is a hack to allow us to restart Failnet if we're running the script through a batch file.
		display('- Removing termination indicator file');
		if(file_exists(FAILNET_ROOT . 'data/restart.inc'))
			unlink(FAILNET_ROOT . 'data/restart.inc');

		// In case of restart/reload, to prevent 'Nick already in use' (which asplodes everything)
		usleep(500); display(array(self::HR, 'Failnet loaded and ready!', self::HR));
	}

	/**
	 * Run Failnet! :D
	 * @return void
	 */
	public function run()
	{
		// Set time limit, we don't want Failnet to time out, at all.
		set_time_limit(0);

		// Now connect to the server
		$this->socket->connect();

		// Toss a connection call to plugins for initial setup
		foreach($this->plugins as $name => $plugin)
		{
			$plugin->cmd_connect();
		}

		// Begin zer loopage!
		while(true)
		{
			$queue = array();

			// Upon each 'tick' of the loop, we call these functions
			foreach($this->plugins as $name => $plugin)
			{
				$plugin->tick();
			}

			// Check for events
			$event = $this->socket->get();
			if($event)
			{
				if($event instanceof failnet_event_response)
				{
					$eventtype = 'response';
				}
				else
				{
					$eventtype = $event->type;
				}
			}

			// Check to see if the user that generated the event is ignored.
			if($eventtype != 'response' && isset($this->ignore) && $this->ignore->ignored($event->hostmask))
				continue;

			// For each plugin, we provide the event encountered so that the plugins can react to them for us
			foreach($this->plugins as $name => $plugin)
			{
				if($event)
				{
					$plugin->event = $event;
					$plugin->pre_event();
					$plugin->{'cmd_' . $eventtype}();
					$plugin->post_event();
				}

				$queue = array_merge($queue, $plugin->events);
				$plugin->events = array();
			}

			// Do we have any events to perform?
			if(!$queue)
				continue;

			//Execute pre-dispatch callback for plugin events
			foreach($this->plugins as $name => $plugin)
			{
				$plugin->pre_dispatch($queue);
			}

			// Time to fire off our events
			$quit = NULL;
			foreach($queue as $item)
			{
				if(strcasecmp($item->type, 'quit') != 0)
				{
					call_user_func_array(array($this->irc, $item->type), $item->arguments);
				}
				elseif (empty($quit))
				{
					$quit = $item;
				}
			}

			// Post-dispatch events
			foreach($this->plugins as $name => $plugin)
			{
				$plugin->post_dispatch($queue);
			}

			// If quit was called, we break out of the cycle and prepare to quit.
			if($quit)
				break;
		}

		foreach($this->plugins as $name => $plugin)
		{
			$plugin->cmd_disconnect();
		}
		$this->irc->quit($this->get('quit_msg'));
		$this->terminate($quit->arguments[0]);
	}

	/**
	 * Failnet configuration file settings load method
	 * @param string $file - The configuration file to load
	 * @return void
	 */
	private function load($file)
	{
		if(!@file_exists(FAILNET_ROOT . $file . '.php') || !@is_readable(FAILNET_ROOT . $file . '.php'))
			throw_fatal("Required Failnet configuration file '$file.php' not found");

		$settings = require FAILNET_ROOT . $file . '.php';

		foreach($settings as $setting => $value)
		{
			if(property_exists($this, $setting))
			{
				$this->$setting = $value;
			}
			else
			{
				$this->config[$setting] = $value;
			}
		}
	}

	/**
	 * Terminates Failnet, and restarts if ordered to.
	 * @param boolean $restart - Should Failnet try to restart?
	 * @return void
	 */
	public function terminate($restart = true)
	{
		if($this->socket->socket !== NULL)
			$this->irc->quit($this->get('quit_msg'));
		if($restart)
		{
			// Just a hack to get it to restart through batch, and not terminate.
			file_put_contents(FAILNET_ROOT . 'data/restart.inc', 'yesh');
			// Dump the log cache to the file.
			$this->log->add('--- Restarting Failnet ---', true);
			display('-!- Restarting Failnet');
			exit(0);
		}
		else
		{
			// Just a hack to get it to truly terminate through batch, and not restart.
			if(file_exists(FAILNET_ROOT . 'data/restart.inc'))
				unlink(FAILNET_ROOT . 'data/restart.inc');
			// Dump the log cache to the file.
			$this->log->add('--- Terminating Failnet ---', true);
			display('-!- Terminating Failnet');
			exit(1);
		}
	}

	/**
	 * Get a setting from Failnet's configuration settings
	 * @param string $setting - The config setting that we want to pull the value for.
	 * @param boolean $config_only - Is this an entry that only appears in the config file?
	 * @return mixed - The setting's value, or null if no such setting.
	 */
	public function get($setting, $config_only = false)
	{
		if(property_exists($this, $setting))
			return $this->$setting;
		if(!$config_only && isset($this->settings[$setting]))
			return $this->settings[$setting];
		if(isset($this->config[$setting]))
			return $this->config[$setting];
		return NULL;
	}

	/**
	 * Unified interface for prepared PDO statements
	 * @param string $table - The table that we are looking at
	 * @param string $type - The type of statement we are looking at
	 * @param mixed $statement - The actual PDO statement that is to be prepared (if we are preparing a statement)
	 * @return mixed - Either an instance of PDO_Statement class (if $statement is false) or void
	 */
	public function sql($table, $type, $statement = false)
	{
		// Retrieve a prepared PDO statement or create one, depending on the value of $statement
		if($statement === false)
		{
			return $this->statements[$table][$type];
		}
		else
		{
			$this->statements[$table][$type] = $this->db->prepare($statement);
		}
	}

	/**
	 * Unified interface for plugins
	 * @param string $mode - The method mode to use
	 * @param string $param - The parameters for the mode, see mode documentation
	 * @return mixed - See mode documentation
	 */
	public function plugin($mode, $param)
	{
		switch ($mode)
		{
			/**
			 * Plugin load mode
			 * @param string $param - The name of the plugin to load, omitting the failnet_plugin_ class prefix
			 * @return boolean - Whether or not the plugin loading was successful
			 */
			case 'load':
				if(is_array($param))
				{
					foreach($param as $plugin)
					{
						$this->plugin('load', $plugin);
					}
				}
				else
				{
					$param = (string) $param;
					if(!$this->plugin('loaded', $param) && $this->plugin('exists', $param))
					{
						$this->plugins_loaded[] = $param;
						$plugin = 'failnet_plugin_' . $param;
						$this->plugins[] = new $plugin($this);
						return true;
					}
					return false; // No double-loading of plugins.
				}
			break;

			/**
			 * Checks to see if a plugin has been loaded already
			 * @param string $param - The name of the plugin to check, omitting the failnet_plugin_ class prefix
			 * @return boolean - Was the plugin already loaded?
			 */
			case 'loaded':
				return in_array((string) $param, $this->plugins_loaded);
			break;

			/**
			 * Checks to see if a plugin exists
			 * @param string $plugin - The name of the plugin to check, omitting the failnet_plugin_ class prefix
			 * @return boolean - Does the plugin exist?
			 */
			case 'exists':
				$file = FAILNET_ROOT . '/includes/plugin/' . basename((string) $param) . '.php';
				return (file_exists($file) && is_readable($file));
			break;
		}
	}
	
	/**
	 * Undefined function handler
	 * @param $funct - Function name
	 * @param $params - Function parameters
	 * @return void
	 */
	public function __call($funct, $params)
	{
		trigger_error('Call to undefined method "' . $name . '" in class "' . __CLASS__ . '"', E_USER_WARNING);
	}

	/**
	 * Magic method __get() to use for referencing specific module classes, used to return the property desired
	 * @param string $name - The name of the module class to use
	 * @return object - The object we want to use, or void.
	 */
	public function __get($name)
	{
		if(array_key_exists($name, $this->nodes))
		{
			return $this->nodes[$name];
		}
		else
		{
			$trace = dump_backtrace();
			trigger_error('Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] .' on line ' . $trace[0]['line'] . '--', E_USER_WARNING);
		}
	}

	/**
	 * Magic method __set() to use for referencing specific module classes, used to set a specific property
	 * @param string $name - The name of the module class to use
	 * @param mixed $value - What we want to set this to
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->nodes[$name] = $value;
	}

	/**
	 * Magic method __isset() to use for referencing specific module classes, used to check if a certain property is set
	 * @param string $name - The name of the module class to use
	 * @return boolean - Whether or not the property is set.
	 */
	public function __isset($name)
	{
		 return isset($this->nodes[$name]);
	}

	/**
	 * Magic method __isset() to use for referencing specific module classes, used to unset a certain property
	 * @param string $name - The name of the module class to use
	 * @return void
	 */
	public function __unset($name)
	{
		unset($this->nodes[$name]);
	}

	/**
	* Return unique id
	* @param string $extra additional entropy
	* @return string - The unique ID
	*
	* @author (c) 2007 phpBB Group
	*/
	public function unique_id($extra = 'c')
	{
		static $dss_seeded = false;

		$rand_seed = $this->get('rand_seed');
		$last_rand_seed = $this->get('last_rand_seed');

		$val = md5($rand_seed . microtime());
		$rand_seed = md5($rand_seed . $val . $extra);

		if($dss_seeded !== true && ($last_rand_seed < time() - rand(1,10)))
		{
			$this->sql('config', 'update')->execute(array(':name' => 'rand_seed', ':value' => $rand_seed));
			$this->settings['rand_seed'] = $rand_seed;
			$last_rand_seed = time();
			$this->sql('config', 'update')->execute(array(':name' => 'last_rand_seed', ':value' => $last_rand_seed));
			$this->settings['last_rand_seed'] = $last_rand_seed;
			$dss_seeded = true;
		}

		return substr($val, 4, 16);
	}
	
	// @todo move these methods out of the core

	/**
	 * Deny function...
	 * @return string - The deny message to use. :3
	 */
	public function deny()
	{
		$rand = rand(0, 9);
		switch($rand)
		{
			case 0:
			case 1:
				return 'No.';
			break;
			case 2:
			case 3:
				return 'Uhm, no.';
			break;
			case 4:
			case 5:
				return 'Hells no!';
				break;
			case 6:
			case 7:
			case 8:
				return 'HELL NOEHS!';
			break;
			case 9:
				return 'The number you are dialing is not available at this time.';
			break;
		}
	}

	/**
	 * Checks whether or not a user has a specified status (or if $type is NULL it checks if user is in a specified channel)
	 * @param string $nick - The nick for the user that we are checking
	 * @param string $chan - The channel that we are checking in
	 * @param mixed $type - The type of check to perform, NULL checks for user being in a specified channel.  Use user type constants if not using NULL.
	 * @return mixed - NULL if unhandled $type value, will return false if user is not in the channel or if Failnet is not in the channel, will return boolean true/false according to the check requested.
	 */
	public function user_is($nick, $chan, $type = NULL)
	{
		// Make sure we handle this check first
		if(!in_array($type, array(self::FOUNDER, self::ADMIN, self::OP, self::HALFOP, self::VOICE, self::REGULAR, NULL)))
			return NULL;

		// If it is NULL, we are checking if the user is in the specified channel
		if($type === NULL || $type === self::REGULAR)
			return (isset($this->chans[trim(strtolower($chan))])) ? isset($this->chans[trim(strtolower($chan))][trim(strtolower($nick))]) : false;

		// Okay, we are checking the user type.  Let's do that.
		return isset($this->chans[trim(strtolower($chan))][trim(strtolower($nick))]) && ($this->chans[trim(strtolower($chan))][trim(strtolower($nick))] & $type) != 0;
	}

	/**
	 * Are we directing this at our owner or ourself?
	 * This is best to avoid humilation if we're using an agressive command.  ;)
	 * @param string $user - The user to check.
	 * @return boolean - Are we targeting the owner or ourself?
	 */
	public function checkuser($user)
	{
        if(preg_match('#' . preg_quote($this->get('owner'), '#') . '|' . preg_quote($this->get('nick'), '#') . '|self#i', $user))
            return true;
		return false;
	}

	/**
	 * Get the userlist of a channel
	 * @param string $chan - Channel name
	 * @return mixed - The user list for the channel or false if we don't have the userlist.
	 */
	public function get_users($chan)
	{
		if (isset($this->chans[trim(strtolower($chan))]))
			return array_keys($this->chans[trim(strtolower($chan))]);
		return false;
	}

	/**
	 * Get a random user in a specified channel
	 * @param string $chan - Channel name
	 * @return mixed - Random user's name, or false if we are not in that channel
	 */
	public function random_user($chan)
	{
		$chan = trim(strtolower($chan));
		if (isset($this->chans[$chan]))
		{
			while(array_search(($nick = array_rand($this->chans[$chan], 1)), array('chanserv', 'q', 'l', 's')) !== false) {}
			return $nick;
		}
		return false;
	}
}

?>