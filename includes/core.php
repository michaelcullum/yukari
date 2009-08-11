<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0
 * SVN ID:		$Id$
 * Copyright:	(c) 2009 - Failnet Project
 * License:		http://opensource.org/licenses/gpl-2.0.php  |  GNU Public License v2
 *
 *===================================================================
 * 
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
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
 * @ignore
 */
if(!defined('IN_FAILNET')) exit(1);

/**
 * Failnet - Core class,
 * 		Failnet 2.0 in a nutshell.  Faster, smarter, better, and with a sexier voice. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_core
{
/**
 * Failnet core class properties
 */

	/**
	 * Auth handler
	 * @var failnet_auth object
	 */
	public $auth;

	/**
	 * Database object
	 * @var PDO object
	 */
	public $db;

	/**
	 * Error handler
	 * @var failnet_error object
	 */
	public $error;

	/**
	 * Factoids engine
	 * @var failnet_factoids object
	 */
	public $factoids;

	/**
	 * Ignored users handler
	 * @var failnet_ignore object
	 */
	public $ignore;

	/**
	 * IRC protocol handler
	 * @var failnet_irc object
	 */
	public $irc;

	/**
	 * Logging handler
	 * @var failnet_log object
	 */
	public $log;

	/**
	 * Socket connection handler
	 * @var failnet_socket object
	 */
	public $socket;



	/**
	 * Loaded Failnet plugins
	 * @var array
	 */
	public $plugins = array();

	/**
	 * List of loaded plugins
	 * @var array
	 */
	public $plugins_loaded = array();



	/**
	 * The UNIX timestamp for when Failnet was started
	 * @var integer
	 */
	public $start = 0;

	/**
	 * Our current usernick for the bot
	 * @var string
	 */
	public $nick = '';

	/**
	 * Should we be in debug mode?
	 * @var boolean
	 */
	public $debug = false;

	/**
	 * Are we allowed to speak?
	 * @var boolean
	 */
	public $speak = true;

	/**
	 * Various config settings et al.
	 * @var array
	 */
	public $settings = array();

	/**
	 * What channels are we in, and what users are in them?
	 * @var array
	 */
	public $chans = array();

	/**
	 * Prepared PDO statements for use throughout Failnet
	 * @var array
	 */
	public $statements = array();

	/**
	 * DO NOT _EVER_ CHANGE THIS, FOR THE SAKE OF HUMANITY.  {@link http://xkcd.com/534/ }
	 * @var boolean
	 */
	private $can_become_skynet = FALSE;

/**
 * Failnet core constants
 */
	const HR = '---------------------------------------------------------------------';
	const ERROR_LOG = 'error';
	const USER_LOG = 'user';

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
		if (strtolower(PHP_SAPI) != 'cli')
		{
			if(file_exists(FAILNET_ROOT . 'data/restart.inc')) 
				unlink(FAILNET_ROOT . 'data/restart.inc');
			display('[Fatal Error] Failnet must be run in the CLI SAPI');
			sleep(3);
		    exit(1);
		}

		// Make sure that PDO and the SQLite PDO extensions are loaded, we need them.
		if (!extension_loaded('PDO'))
		{
			if(file_exists(FAILNET_ROOT . 'data/restart.inc')) 
				unlink(FAILNET_ROOT . 'data/restart.inc');
			display('[Fatal Error] Failnet requires the PDO PHP extension to be loaded');
			sleep(3);
		    exit(1);
		}
    	if (!extension_loaded('pdo_sqlite'))
    	{
    		if(file_exists(FAILNET_ROOT . 'data/restart.inc')) 
				unlink(FAILNET_ROOT . 'data/restart.inc');
            display('[Fatal Error] Failnet requires the PDO_SQLite PHP extension to be loaded');
			sleep(3);
		    exit(1);
    	}

		// Check to see if date.timezone is empty in the PHP.ini, if so, set the default timezone to prevent strict errors.
		if (!ini_get('date.timezone'))
			date_default_timezone_set(@date_default_timezone_get());

		// Set the time that Failnet was started.
		$this->start = time();

		// Begin printing info to the terminal window with some general information about Failnet.
		display(array(
			self::HR,
			'Failnet -- PHP-based IRC Bot version ' . FAILNET_VERSION . ' - $Revision$',
			'Copyright: (c) 2009 - Obsidian',
			'License: http://opensource.org/licenses/gpl-2.0.php',
			self::HR,
			'Failnet is starting up. Go get yourself a coffee.',
		));

		// Load the config file
		display('- Loading configuration file for specified IRC server');
		$this->load(($_SERVER['argc'] > 1) ? $_SERVER['argv'][1] : 'config');

		// Load/setup the database
		display('- Loading the Failnet database'); 
		try
		{
			// Initialize the database connection
			$this->db = new PDO('sqlite:' . FAILNET_DB_ROOT . 'failnet.db');
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// We want this as a transaction in case anything goes wrong.
			$this->db->beginTransaction();

			// Check to see if our config table exists...if not, we probably need to install.  o_O
			$failnet_installed = $this->db->query('SELECT COUNT(*) FROM sqlite_master WHERE name = ' . $this->db->quote('config'))->fetchColumn();
			if (!$failnet_installed)
			{
				display(array('- Database tables not installed, installing Failnet', '- Constructing database tables...', ' -  Creating config table...'));
				
				// Make some DB tables here...
				$this->db->exec(file_get_contents(FAILNET_ROOT . 'includes/schemas/config.sql'));
				display(' -  Creating users table...');
				$this->db->exec(file_get_contents(FAILNET_ROOT . 'includes/schemas/users.sql'));
				display(' -  Creating sessions table...');
				$this->db->exec(file_get_contents(FAILNET_ROOT . 'includes/schemas/sessions.sql'));
				display(' -  Creating access table...');
				$this->db->exec(file_get_contents(FAILNET_ROOT . 'includes/schemas/access.sql'));
				display(' -  Creating ignored hostmasks table...');
				$this->db->exec(file_get_contents(FAILNET_ROOT . 'includes/schemas/ignore.sql'));
				display('- Database table creation complete.');
			}

			display('- Preparing database...');
			
			// Now, we need to build our default statements.
			// Config table
			$this->build_sql('config', 'create', 'INSERT INTO config ( name, value ) VALUES ( ":name", ":value" )');
			$this->build_sql('config', 'get', 'SELECT * FROM config WHERE LOWER(name) = LOWER(":name") LIMIT 1');
			$this->build_sql('config', 'update', 'UPDATE config SET value = :value WHERE LOWER(name) = LOWER(":name")');
			$this->build_sql('config', 'delete', 'DELETE FROM config WHERE name = ":name"');

			// Users table
			$this->build_sql('users', 'create', 'INSERT INTO users ( nick, authlevel, password ) VALUES ( :nick, :authlevel, :hash )');
			$this->build_sql('users', 'set_pass', 'UPDATE users SET password = ":hash" WHERE user_id = :user');
			$this->build_sql('users', 'set_level', 'UPDATE users SET authlevel = :authlevel WHERE user_id = :user');
			$this->build_sql('users', 'set_confirm', 'UPDATE users SET confirm_key = ":key" WHERE user_id = :user');
			$this->build_sql('users', 'get', 'SELECT * FROM users WHERE LOWER(nick) = LOWER(:nick) LIMIT 1');
			$this->build_sql('users', 'get_level', 'SELECT authlevel FROM users WHERE LOWER(nick) = LOWER(:nick) LIMIT 1');
			$this->build_sql('users', 'get_confirm', 'SELECT confirm_key FROM users WHERE user_id = :user LIMIT 1');
			$this->build_sql('users', 'delete', 'DELETE FROM users WHERE user_id = :user');

			// Sessions table
			$this->build_sql('sessions', 'create', 'INSERT INTO sessions ( key_id, user_id, login_time, hostmask ) VALUES ( ":key", :user, :time, ":hostmask" )');
			$this->build_sql('sessions', 'delete_key', 'DELETE FROM sessions WHERE key_id = :key');
			$this->build_sql('sessions', 'delete_user', 'DELETE FROM sessions WHERE user_id = :user');
			$this->build_sql('sessions', 'delete_old', 'DELETE FROM sessions WHERE login_time < :time');
			$this->build_sql('sessions', 'delete', 'DELETE FROM sessions WHERE LOWER(hostmask) = LOWER(:hostmask)');
			
			// Access list table
			$this->build_sql('access', 'create', 'INSERT INTO access ( user_id, hostmask ) VALUES ( :user, ":hostmask" )');
			$this->build_sql('access', 'delete', 'DELETE FROM access WHERE (user_id = :user AND LOWER(hostmask) = LOWER(:hostmask) )');
			$this->build_sql('access', 'delete_user', 'DELETE FROM access WHERE user_id = :user');
			$this->build_sql('access', 'get', 'SELECT hostmask FROM access WHERE user_id = :user');

			// Commit the stuffs
			$this->db->commit();
		}
		catch (PDOException $e)
		{
			// Something went boom.  Time to panic!
			$this->db->rollBack();
			if(file_exists(FAILNET_ROOT . 'data/restart.inc')) 
				unlink(FAILNET_ROOT . 'data/restart.inc');
			trigger_error($e, E_USER_WARNING);
			sleep(3);
			exit(1);
		}

		// Load required classes and systems
		display('- Loading Failnet required classes');
		$classes = array(
			'socket'	=> 'connection interface handler',
			'irc'		=> 'IRC protocol handler',
			'log'		=> 'event logging handler',
			'error'		=> 'error handler',
			'auth'		=> 'user authorization handler',
			'ignore'	=> 'user ignore handler',
			'factoids'	=> 'factoid engine',
		);
		foreach($classes as $class => $msg)
		{
			if(property_exists(__CLASS__, $class))
			{
				$name = 'failnet_' . $class;
				$this->$class = new $name($this);
				display('=-= Loaded ' . $msg . ' class');
			}
		}

		// Set the error handler
		display('=== Setting main error handler');
		@set_error_handler(array(&$this, 'error', 'fail'));

		// If Failnet was just installed, we need to do something now that the auth class is loaded
		if (!$failnet_installed)
		{
			try
			{
				$this->db->beginTransaction();

				// Add the default user if Failnet was just installed
				$this->sql('users', 'create')->execute(array(':nick' => $this->get('owner'), ':authlevel' => 100, ':hash' => $this->auth->hash->hash($this->get('name'))));

				// Now let's add some default data to the database tables
				$this->db->exec(file_get_contents(FAILNET_ROOT . 'includes/schemas/schema_data.sql'));

				$this->db->commit();
			}
			catch (PDOException $e)
			{
				// Roll back ANY CHANGES MADE, something went boom.
				$this->db->rollBack();
				if(file_exists(FAILNET_ROOT . 'data/restart.inc')) 
					unlink(FAILNET_ROOT . 'data/restart.inc');
				trigger_error($e, E_USER_WARNING);
				sleep(3);
				exit(1);
			}
		}

		// Load plugins
		display('- Loading Failnet plugins');
		$plugins = $this->get('plugin_list');
		$this->load_plugins($plugins);

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
		foreach ($this->plugins as $name => $plugin)
		{
			$plugin->cmd_connect();
		}

		// Begin zer loopage!
		while(true)
		{
			$queue = array();

			// Upon each 'tick' of the loop, we call these functions
			foreach ($this->plugins as $name => $plugin)
			{
				$plugin->tick();
			}

			// Check for events
			$event = $this->socket->get();
			if ($event)
			{
				if ($event instanceof failnet_event_response)
				{
					$eventtype = 'response';
				}
				else
				{
					$eventtype = $event->type;
				}
			}

			// Check to see if the user that generated the event is ignored.
			if($eventtype != 'response' && $this->ignore->ignored($event->gethostmask()))
				continue;

			// For each plugin, we provide the event encountered so that the plugins can react to them for us  
			foreach ($this->plugins as $name => $plugin)
			{
				if ($event)
				{
					$plugin->event = $event;
					$plugin->pre_event();
					$plugin->{'cmd_' . $eventtype}();
					$plugin->post_event();
					if($this->debug) 
						display($eventtype . ': ' . $name. ' ' . count($plugin->events));
				}

				$queue = array_merge($queue, $plugin->events);
				$plugin->events = array();
			}

			// Do we have any events to perform?
			if (!$queue)
				continue;

			//Execute pre-dispatch callback for plugin events 
			foreach ($this->plugins as $name => $plugin)
			{
				if($this->debug)
					display('pre-dispatch: ' . $name . ' ' . count($queue));
				$plugin->pre_dispatch($queue);
			}

			// Time to fire off our events
			$quit = NULL;
			foreach ($queue as $item)
			{
				if($this->debug)
					display($item->type);
				if (strcasecmp($item->type, 'quit') != 0)
				{
					call_user_func_array(array($this->irc, $item->type), $item->arguments);
				}
				elseif (empty($quit))
				{
					$quit = $item;
				}
			}

			// Post-dispatch events
			foreach ($this->plugins as $name => $plugin)
			{
				if($this->debug)
					display('post-dispatch: ' . $name . ' ' . count($queue));
				$plugin->post_dispatch($queue);
			}

			// If quit was called, we break out of the cycle and prepare to quit.
			if ($quit)
				break;
		}

		foreach ($this->plugins as $name => $plugin)
		{
			if($this->debug)
				display('disconnect: ' . $name);
			$plugin->cmd_disconnect();
		}
		$this->terminate($quit->arguments[0]);
	}

	/**
	 * Failnet configuration file settings load method
	 * @param string $file - The configuration file to load
	 * @return void 
	 */
	private function load($file)
	{
		if(!file_exists(FAILNET_ROOT . $file . '.php') || !is_readable(FAILNET_ROOT . $file . '.php'))
			trigger_error('Required Failnet configuration file [' . $file . '.php] not found', E_USER_ERROR);

		$settings = require FAILNET_ROOT . $file . '.php';

		foreach($settings as $setting => $value)
		{
			if(property_exists($this, $setting))
			{
				$this->$setting = $value;
			}
			else
			{
				$this->settings[$setting] = $value;
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
	 * @return mixed - The setting's value, or null if no such setting.
	 */
	public function get($setting)
	{
		if(property_exists($this, $setting))
			return $this->$setting;
		if(isset($this->settings[$setting]))
			return $this->settings[$setting];
		return NULL;
	}

	/**
	 * Retrieve a prepared PDO statement for the Failnet core DB tables.
	 * @param $table - The table that we are pulling the statement from
	 * @param $type - The type of statement we are pulling
	 * @return object - An instance of PDO_Statement
	 */
	public function sql($table, $type)
	{
		return $this->statements[$table][$type];
	}

	/**
	 * Builds a prepared PDO Statement and stores it internally. 
	 * @param $table - The table that we are making the statement for
	 * @param $type - What is the statement for?
	 * @param $statement - The actual PDO statement that is to be prepared
	 * @return void
	 */
	public function build_sql($table, $type, $statement)
	{
		$this->statements[$table][$type] = $this->db->prepare($statement);
	}

	/**
	 * Load a specific plugin
	 * @param string $plugin - The name of the plugin to load, omitting the failnet_plugin_ class prefix 
	 * @return boolean - If plugin loading was successful
	 */
 	public function load_plugin($plugin)
	{
		if(!$this->plugin_loaded($plugin))
		{
			$this->plugins_loaded[] = $plugin;
			$plugin = 'failnet_plugin_' . $plugin;
			$this->plugins[] = new $plugin($this);
			return true;
		}
		return false; // No double-loading of plugins.
	}

	/**
	 * Load an array of plugins
	 * @param array $plugins - An array of plugin names, omitting the failnet_plugin_ class prefix
	 * @return void
	 */
	public function load_plugins(array $plugins)
	{
		foreach ($plugins as $plugin)
		{
			$this->load_plugin($plugin);
		}
	}

	/**
	 * Checks to see if a plugin has been loaded already
	 * @param string $plugin - The name of the plugin to check, omitting the failnet_plugin_ class prefix
	 * @return boolean - Was the plugin already loaded?
	 */
	public function plugin_loaded($plugin)
	{
		return in_array($plugin, $this->plugins_loaded);
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
		
		$rand_seed = $this->sql('config', 'get')->execute(array(':name' => 'rand_seed'));
		$last_rand_seed = $this->sql('config', 'get')->execute(array(':name' => 'last_rand_seed'));
		$val = md5($rand_seed . microtime());
		$rand_seed = md5($rand_seed . $val . $extra);
	
		if ($dss_seeded !== true && ($last_rand_seed < time() - rand(1,10)))
		{
			$this->sql('config', 'get')->execute(array(':name' => 'rand_seed', ':value' => $rand_seed));
			$this->sql('config', 'update')->execute(array(':name' => 'last_rand_seed', ':value' => time()));
			$dss_seeded = true;
		}
	
		return substr($val, 4, 16);
	}

	/**
	 * Deny function...
	 * @return string - The deny message to use. :3
	 */
	public function deny()
	{
		$rand = rand(0, 9);
		switch ($rand)
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
	 * Checks whether or not a given user has founder (~) status.
	 * @param string $nick User nick to check
	 * @param string $chan Channel to check in
	 * @return bool
	 */
	public function is_founder($nick, $chan)
	{
		return isset($this->chans[trim(strtolower($chan))][trim(strtolower($nick))]) && ($this->chans[trim(strtolower($chan))][trim(strtolower($nick))] & self::FOUNDER) != 0;
	}

	/**
	 * Checks whether or not a given user has admin (&) status.
	 * @param string $nick User nick to check
	 * @param string $chan Channel to check in
	 * @return bool
	 */
	public function is_admin($nick, $chan)
	{
		return isset($this->chans[trim(strtolower($chan))][trim(strtolower($nick))]) && ($this->chans[trim(strtolower($chan))][trim(strtolower($nick))] & self::ADMIN) != 0;
	}

	/**
	 * Checks whether or not a given user has op (@) status.
	 * @param string $nick User nick to check
	 * @param string $chan Channel to check in
	 * @return bool
	 */
	public function is_op($nick, $chan)
	{
		return isset($this->chans[trim(strtolower($chan))][trim(strtolower($nick))]) && ($this->chans[trim(strtolower($chan))][trim(strtolower($nick))] & self::OP) != 0;
	}

	/**
	 * Checks whether or not a given user has halfop (%) status.
	 * @param string $nick User nick to check
	 * @param string $chan Channel to check in
	 * @return bool
	 */
	public function is_halfop($nick, $chan)
	{
		return isset($this->chans[trim(strtolower($chan))][trim(strtolower($nick))]) && ($this->chans[trim(strtolower($chan))][trim(strtolower($nick))] & self::HALFOP) != 0;
	}

	/**
	 * Checks whether or not a given user has voice (+) status.
	 * @param string $nick User nick to check
	 * @param string $chan Channel to check in
	 * @return bool
	 */
	public function is_voice($nick, $chan)
	{
		return isset($this->chans[trim(strtolower($chan))][trim(strtolower($nick))]) && ($this->chans[trim(strtolower($chan))][trim(strtolower($nick))] & self::VOICE) != 0;
	}

	/**
	 * Checks whether or not a particular user is in a particular channel.
	 * @param string $nick User nick to check
	 * @param string $chan Channel to check in
	 * @return bool
	 */
	public function is_in($nick, $chan)
	{
		if(isset($this->chans[trim(strtolower($chan))]))
			return isset($this->chans[trim(strtolower($chan))][trim(strtolower($nick))]);
		return false;
	}
	
	/**
	 * Are we directing this at our owner or ourself?
	 * This is best to avoid humilation if we're using an agressive factoid.  ;)
	 * @param $user - The user to chech.
	 * @return boolean - Are we targeting the owner or ourself?
	 */
	public function checkuser($user)
	{
		return (!preg_match('#' . preg_quote($this->owner, '#') . '#is', $user) && !preg_match('#' . preg_quote($this->nick, '#') . '#is', $user) && !preg_match('#self#i', $user)) ? true : false;
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
			while (array_search(($nick = array_rand($this->chans[$chan], 1)), array('chanserv', 'q', 'l', 's')) !== false) {}
			return $nick;
		}
		return false;
	}
	
	/**
	 * Undefined function handler
	 * @param $funct - Function name
	 * @param $params - Function parameters
	 * @return void
	 */
	public function __call($funct, $params)
	{
		trigger_error('Bad function call "' . $funct . '" with params "' . implode(', ', $params) . '" to "' . get_class() . ' class.', E_USER_WARNING);
	}
}

?>