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
	public $auth;
	public $error;
	public $factoids;
	public $irc;
	public $ignore;
	public $log;
	public $socket;
	
	// Failnet settings and stuff.
	public $debug = false;
	public $warlord = false;
	public $speak = true;

	// Server connection and config vars.
	public $server = '';
	public $port = 6667;

	// Configs for Failnet's authorization and stuff.
	public $owner = '';
	public $nick = '';
	public $pass = '';
	public $user = 'Failnet';
	public $name = 'Failnet';
	
	// DO NOT CHANGE.
	public $original = '';
	
	// What channels are we moderating?
	public $war_chans = array(); 

	// Modules list.
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
		    exit(1);
		}
		
		/**
		 * Check to see if date.timezone is empty in the PHP.ini, if so, set the default timezone to prevent strict errors.
		 */
		if (!ini_get('date.timezone')) 
			date_default_timezone_set(date_default_timezone_get());
		
		// Set time limit!
		set_time_limit(0);
		
		// Begin printing info to the terminal window with some general information about Failnet.
		display(array(
			failnet_common::HR,
			'Failnet -- PHP-based IRC Bot version ' . FAILNET_VERSION . ' - $Revision$',
			'Copyright: (c) 2009 - Obsidian',
			'License: http://opensource.org/licenses/gpl-2.0.php',
			failnet_common::HR,
			'Failnet is starting up. Go get yourself a coffee.',
		));
		
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
			'auth'		=> 'user authorization handler',
			'ignore'	=> 'user/hostmask ignore handler',
			'factoids'	=> 'factoid handler',
		);
		display('- Loading Failnet required classes');
		foreach($classes as $class => $msg)
		{
			if(property_exists($class)
			{
				$this->$class = new $class();
				display('=-= Loaded ' . $msg . ' class');
			}
		}
		
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
		
		display('- Loading configuration file for specified IRC server');
			$this->load($_SERVER['argc'] > 1 ? $_SERVER['argv'][1] : 'config');
		
		display('Preparing to connect...'); sleep(1); // In case of restart/reload, to prevent 'Nick already in use' (which asplodes everything)
		display(array('Failnet loaded and ready!', failnet_common::HR));
	}
	
	public function load($file)
	{
		if(!file_exists(FAILNET_ROOT . $file . '.' . PHP_EXT) || !is_readable(FAILNET_ROOT . $file . '.' . PHP_EXT))
			$this->error->error('Required Failnet configuration file [' . $file . '.' . PHP_EXT . '] not found', true);
		$settings = require FAILNET_ROOT . $file . '.' . PHP_EXT;
		// @todo: FINISH THIS SHIZ.
	}
	
	public function run()
	{
		// @todo: WRITE THIS BEAST.
	}
	
	// Terminates Failnet, and restarts if ordered to.
	public function terminate($restart = true)
	{
		if($this->socket->socket !== NULL)
			$this->socket->quit('Failnet PHP IRC Bot');
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