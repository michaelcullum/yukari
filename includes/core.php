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
if(!defined('IN_FAILNET')) return;

class failnet_core extends failnet_common
{
	/**
	 * Object vars for Failnet's use
	 */
	public $error;
	public $irc;
	public $log;
	public $manager;
	public $plugins;
	public $socket;
	
	// Failnet settings and stuff.
	public $debug = false;
	public $speak = true;

	// Server connection and config vars.
	public $server = '';
	public $port = 6667;

	// Configs for Failnet's authorization and stuff.
	public $owner = '';
	public $nick = '';
	public $pass = '';

	public $settings = array();

		/*  Moved to $this->settings
		 * 
			public $altnicks = array();
			public $server_pass = '';
			public $user = '';
			public $name = '';
			public $intro_msg = '';
			public $restart_msg = '';
			public $dai_msg = '';
			public $quit_msg = '';
		*/
	
		// @todo: MOVE THIS SHIZ TO PLUGINS!
		public $auth; // Move to plugin
		public $factoids; // Move to plugin
		public $ignore; // Move to plugin
		// What channels are we moderating?
		public $war_chans = array(); 
		public $warlord = false;

		// Modules list.
		// Convert these to plugins.
		public $modules = array();
		public $help = array();
	
	public function init()
	{
		/**
		 * Check to make sure the CLI SAPI is being used...
		 */
		if (strtolower(PHP_SAPI) != 'cli')
		{
			if(file_exists(FAILNET_ROOT . 'data/restart')) 
				unlink(FAILNET_ROOT . 'data/restart');
			display('Failnet must be run in the CLI SAPI');
			sleep(3);
		    exit(1);
		}
		
		/**
		 * Check to see if date.timezone is empty in the PHP.ini, if so, set the default timezone to prevent strict errors.
		 */
		if (!ini_get('date.timezone')) 
			date_default_timezone_set(date_default_timezone_get());
		
		/**
		 *  Begin printing info to the terminal window with some general information about Failnet.
		 */
		display(array(
			failnet_common::HR,
			'Failnet -- PHP-based IRC Bot version ' . FAILNET_VERSION . ' - $Revision$',
			'Copyright: (c) 2009 - Obsidian',
			'License: http://opensource.org/licenses/gpl-2.0.php',
			failnet_common::HR,
			'Failnet is starting up. Go get yourself a coffee.',
		));
		
		display('- Loading configuration file for specified IRC server');
		$this->load($_SERVER['argc'] > 1 ? $_SERVER['argv'][1] : 'config');
		
		display('- Loading dictionary (if file is present on OS)'); 
		$dict = (@file_exists('/etc/dictionaries-common/words')) ? file('/etc/dictionaries-common/words') : array();
			display('- Loading Failnet core information');
			$this->modules[] = 'core';
			$this->help['core'] = 'For help with the core system, please reference this site: http://www.assembla.com/wiki/show/failnet/';
		
		$classes = array(
			'socket'	=> 'connection interface handler',
			'irc'		=> 'IRC protocol handler',
			'log'		=> 'event logging handler',
			'error'		=> 'error handler',
			'manager'	=> 'plugin handler',
			'auth'		=> 'user authorization handler',
			'ignore'	=> 'user/hostmask ignore handler',
			'factoids'	=> 'factoid handler',
		);
		
		display('- Loading Failnet required classes');
		foreach($classes as $class => $msg)
		{
			if(property_exists($class)
			{
				$this->$class = new $class($this);
				display('=-= Loaded ' . $msg . ' class');
			}
		}
		
		// @todo: FINISH THIS.
		display('Loading Failnet plugins');
		$plugins = $this->get('plugin_list');
		foreach($plugins as $plugin)
		{
			$this->manager->load($plugin);
			display('=-= Loaded ' . $plugin . ' plugin');
		}
		
			// @todo: MAKE THIS SHIZ PLUGINS.
			// Load modules
			$load = array(
				'simple_html_dom',
				'warfare',
				'slashdot',
				'xkcd',
			/*
				'alchemy',
				'notes',
			*/
			);
			display('- Loading modules');
			foreach($load as $item)
			{
				if(include FAILNET_ROOT . 'modules' . DIRECTORY_SEPARATOR . $item . '.' . PHP_EXT)
					display('=-= Loaded "' . $item . '" module');
			}
		
		// This is a hack to allow us to restart Failnet if we're running the script through a batch file.
		display('- Removing termination indicator file'); 
		if(file_exists(FAILNET_ROOT . 'data/restart')) 
			unlink(FAILNET_ROOT . 'data/restart');
		
		display('Preparing to connect...'); sleep(1); // In case of restart/reload, to prevent 'Nick already in use' (which asplodes everything)
		display(array('Failnet loaded and ready!', failnet_common::HR));
	}
	
	/**
	 * Failnet configuration file settings load method
	 */
	public function load($file)
	{
		if(!file_exists(FAILNET_ROOT . $file . '.' . PHP_EXT) || !is_readable(FAILNET_ROOT . $file . '.' . PHP_EXT))
			$this->error->error('Required Failnet configuration file [' . $file . '.' . PHP_EXT . '] not found', true);
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
	 */
	public function get($setting)
	{
		if(property_exists($this, $setting))
			return $this->$setting;
		return $this->settings[$setting];
	}

	/**
	 * Run Failnet.
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
			$events = array();
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
					$plugin->$eventtype();
					$plugin->post_event();
					if($this->debug) 
						display($eventype . ': ' . $name. ' ' . count($plugin->events);
				}

				$events = array_merge($events, $plugin->events;
				$plugin->events = array();
			}

			if (!$events)
				continue;

			//Execute pre-dispatch callback for plugin events 
			foreach ($this->plugins as $name => $plugin)
			{
				$plugin->preDispatch($events);
				if($this->debug)
					display('pre-dispatch: ' . $name. ' ' . count($events));
			}
			
			$quit = NULL;
			foreach ($events as $event)
			{
				if($this->debug)
					display($event->type);
				if (strcasecmp($event->type(), 'quit') != 0)
				{
					call_user_func_array(array($this->irc, $event->type), $event->arguments());
				}
				elseif (empty($quit))
				{
					$quit = $event;
				}
			}

			foreach ($this->plugins as $name => $plugin)
			{
				if($this->debug)
					display('post-dispatch: ' . $name);
				$plugin->post_dispatch($events);
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
	
	// Terminates Failnet, and restarts if ordered to.
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
}

?>