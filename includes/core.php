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
 * Copyright:	(c) 2009 - Obsidian
 * License:		http://opensource.org/licenses/gpl-2.0.php  |  GNU Public License v2
 *
 *===================================================================
 * 
 */

// @todo failnet_core::no_factoid() method, for saying something when there's no factoid available for that.

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

class failnet_core
{
	/**
	 * Object vars for Failnet's use
	 */
	public $auth;
	public $error;
	public $factoids;
	public $irc;
	public $log;
	public $manager;
	public $socket;
	
	// Failnet settings and stuff.
	public $start = 0;
	public $debug = false;
	public $speak = true;
	public $chans = array();
	public $ignore = array();
	public $settings = array();
	public $plugins = array();

	// Server connection and config vars.
	public $server = '';
	public $port = 6667;

	// Configs for Failnet's authorization and stuff.
	public $owner = '';
	public $nick = '';
	public $pass = '';
	
	/**
	 * Constants for Failnet.
	 */
	const TAB = "\t";
	const X01 = "\x01";
	const X02 = "\x02";
	const HR = '---------------------------------------------------------------------';
	const ERROR_LOG = 'error';
	const USER_LOG = 'user';
	
	public function __construct()
	{
		// Check to make sure the CLI SAPI is being used...
		if (strtolower(PHP_SAPI) != 'cli')
		{
			if(file_exists(FAILNET_ROOT . 'data/restart')) 
				unlink(FAILNET_ROOT . 'data/restart');
			display('Failnet must be run in the CLI SAPI');
			sleep(3);
		    exit(1);
		}
		
		// Check to see if date.timezone is empty in the PHP.ini, if so, set the default timezone to prevent strict errors.
		if (!ini_get('date.timezone'))
			date_default_timezone_set(date_default_timezone_get());
		
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
		
		display('- Loading configuration file for specified IRC server');
		$this->load(($_SERVER['argc'] > 1) ? $_SERVER['argv'][1] : 'config');
		
		$classes = array(
			'socket'	=> 'connection interface handler',
			'irc'		=> 'IRC protocol handler',
			'log'		=> 'event logging handler',
			'error'		=> 'error handler',
			'manager'	=> 'plugin handler',
			'auth'		=> 'user authorization handler',
			'factoids'	=> 'factoid handler',
		);
		
		display('- Loading Failnet required classes');
		foreach($classes as $class => $msg)
		{
			if(property_exists($class))
			{
				$this->$class = new $class($this);
				display('=-= Loaded ' . $msg . ' class');
			}
		}

		display('Loading Failnet plugins');
		$plugins = $this->get('plugin_list');
		foreach($plugins as $plugin)
		{
			$this->manager->load($plugin);
			display('=-= Loaded ' . $plugin . ' plugin');
		}
		
		// This is a hack to allow us to restart Failnet if we're running the script through a batch file.
		display('- Removing termination indicator file'); 
		if(file_exists(FAILNET_ROOT . 'data/restart')) 
			unlink(FAILNET_ROOT . 'data/restart');
		
		// In case of restart/reload, to prevent 'Nick already in use' (which asplodes everything)
		display('Preparing to connect...'); usleep(500);
		display(array('Failnet loaded and ready!', failnet_common::HR));
	}
	
	/**
	 * Failnet configuration file settings load method
	 */
	public function load($file)
	{
		if(!file_exists(FAILNET_ROOT . $file . '.' . PHP_EXT) || !is_readable(FAILNET_ROOT . $file . '.' . PHP_EXT))
			trigger_error('Required Failnet configuration file [' . $file . '.' . PHP_EXT . '] not found', E_USER_ERROR);

		$settings = require FAILNET_ROOT . $file . '.' . PHP_EXT;

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
		// ...Is this it?  O_o
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
	 * Run Failnet.
	 * @return void
	 */
	public function run()
	{
		// Set time limit!
		set_time_limit(0);

		$this->socket->connect();
		foreach ($this->plugins as $name => $plugin)
		{
			$plugin->connect();
		}
		
		// Begin zer loopage!
		while(true)
		{
			$queue = array();
			foreach ($this->plugins as $name => $plugin)
			{
				$plugin->tick();
			}

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
			
			// For each plugin... 
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

			if (!$events)
				continue;

			//Execute pre-dispatch callback for plugin events 
			foreach ($this->plugins as $name => $plugin)
			{
				if($this->debug)
					display('pre-dispatch: ' . $name . ' ' . count($queue));
				$plugin->pre_dispatch($queue);
			}
			
			$quit = NULL;
			foreach ($queue as $item)
			{
				if($this->debug)
					display($item->type);
				if (strcasecmp($item->type(), 'quit') != 0)
				{
					call_user_func_array(array($this->irc, $item->type), $item->arguments());
				}
				elseif (empty($quit))
				{
					$quit = $item;
				}
			}

			foreach ($this->plugins as $name => $plugin)
			{
				if($this->debug)
					display('post-dispatch: ' . $name . ' ' . count($queue));
				$plugin->post_dispatch($queue);
			}

			if ($quit)
			{
				call_user_func_array(array($this->socket, 'quit'), $quit->arguments());
				foreach ($this->plugins as $name => $plugin)
				{
					if($this->debug)
						display('disconnect: ' . $name);
					$plugin->disconnect();
				}
				break;
			}
		}
		$this->terminate(false);
	}
	
	/**
	 * Terminates Failnet, and restarts if ordered to.
	 * @param boolean $restart - Should Failnet try to restart?
	 * @return void
	 */
	public function terminate($restart = true)
	{
		if($this->socket->socket !== NULL)
			$this->socket->quit($this->get('quit_msg'));
		if($restart)
		{
			// Just a hack to get it to restart through batch, and not terminate.
			file_put_contents('data/restart', 'yesh');
			// Dump the log cache to the file.
			$this->log->add('--- Restarting Failnet ---', true);
			display('-!- Restarting Failnet');
			exit(0);
		}
		else
		{
			// Just a hack to get it to truly terminate through batch, and not restart.
			if(file_exists(FAILNET_ROOT . 'data/restart')) 
				unlink(FAILNET_ROOT . 'data/restart');
			// Dump the log cache to the file.
			$this->log->add('--- Terminating Failnet ---', true);
			display('-!- Terminating Failnet');
			exit(1);
		}
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
	 * Checks to see if a given hostmask is ignored by using a PCRE regex on the ignore list...
	 * @param string $host - The hostmask to check
	 * @return boolean - True if ignored, false if not ignored or if no ignore list (might be because ignore plugin is not loaded).
	 */
	public function ignored($host)
	{
		if(empty($this->ignore))
			return false;
		return preg_match(hostmasks_to_regex($this->ignore), $host);
	}
	
	/**
	 * Checks whether or not a given user has op (@) status.
	 *
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
	 *
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
	 *
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
	 *
	 * @param string $nick User nick to check
	 * @param string $chan Channel to check in
	 * @return bool
	 */
	public function is_in($nick, $chan)
	{
		return isset($this->chans[trim(strtolower($chan))][trim(strtolower($nick))]);
	}
	
	/**
	 * Are we directing this at our owner or ourself?
	 * This is best to avoid humilation if we're using an agressive factoid.  ;)
	 * 
	 * @param $user - The user to chech.
	 * @return boolean - Are we targeting the owner or ourself?
	 */
	public function checkuser($user)
	{
		return (!preg_match('#' . preg_quote($this->owner, '#') . '#is', $user) && !preg_match('#' . preg_quote($this->nick, '#') . '#is', $user) && !preg_match('#self#i', $user)) ? true : false;
	}

	/**
	 * Returns the entire user list for a channel or false if the bot is not
	 * present in the channel.
	 *
	 * @param string $chan Channel name
	 * @return array|bool
	 */
	public function get_users($chan)
	{
		if (isset($this->chans[trim(strtolower($chan))]))
			return array_keys($this->chans[trim(strtolower($chan))]);
		return false;
	}

	/**
	 * Returns the nick of a random user present in a given channel or false
	 * if the bot is not present in the channel.
	 *
	 * @param string $chan Channel name
	 * @return string|bool
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