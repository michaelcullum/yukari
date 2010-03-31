<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * @version:	2.1.0 DEV
 * @copyright:	(c) 2009 - 2010 -- Failnet Project
 * @license:	http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
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
 * Failnet - Terminal UI class,
 * 		Used to handle displaying Failnet's output to a terminal/command prompt.
 *
 *
 * @package core
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_ui extends failnet_common
{
	/**
	 * @var string - Buffer of the stuff we are going to process
	 */
	public $buffer = '';

	/**
	 * @var integer - Our current output level
	 */
	public $output_level = 0;

	/**
	 * Specialized init function to allow class construction to be easier.
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	public function init() { }

	/**
	 * Method that handles output of all data for the UI.
	 * @return void
	 */
	public function output($data)
	{
		echo ((strrpos($data, PHP_EOL . PHP_EOL) !== false) ? substr($data, 0, strlen($data) - 1) : $data) . PHP_EOL;
	}

	/**
	 * Determine if this message type should be sent with the current output level.
	 * @param const $level - The OUTPUT level constant that we are checking the current output level against.
	 * @return boolean - Whether we should output or not...boolean true if so, boolean false if not.
	 */
	public function level($level)
	{
		return (($level != OUTPUT_RAW) ? ($this->output_level >= $level && $this->output_level !== OUTPUT_RAW) : ($this->output_level === OUTPUT_RAW));
	}

	/**
	 * Method called on init that dumps the startup text for Failnet to output
	 * @return void
	 */
	public function startup()
	{
		if($this->level(OUTPUT_NORMAL))
		{
			$this->output('---------------------------------------------------------------------');
			$this->output('Failnet -- PHP-based IRC Bot version ' . FAILNET_VERSION);
			$this->output('Copyright: (c) 2009 - 2010 -- Obsidian');
			$this->output('License: GNU General Public License - Version 2');
			$this->output('---------------------------------------------------------------------');
			$this->output('Failnet is starting up. Go get yourself a coffee.');
		}
	}

	/**
	 * Method called that dumps Failnet's ready-notice text to output
	 * @return void
	 */
	public function ready()
	{
		if($this->level(OUTPUT_NORMAL))
		{
			$this->output('---------------------------------------------------------------------');
			$this->output('Failnet loaded and ready!');
			$this->output('---------------------------------------------------------------------');
		}
	}

	/**
	 * Method called on shutdown that dumps the shutdown text for Failnet to output
	 * @return void
	 */
	public function ui_shutdown()
	{
		if($this->ui_level(OUTPUT_NORMAL))
		{
			$this->output('---------------------------------------------------------------------');
			$this->output('Failnet shutting down...');
			$this->output('---------------------------------------------------------------------');
		}
	}

	/**
	 * Method called on message being recieved/sent
	 * @return void
	 */
	public function message($data)
	{
		if($this->level(OUTPUT_NORMAL))
		{
			$this->output('[irc] ' . $data);
		}
	}

	/**
	 * Method called when a system event is triggered or occurs in Failnet
	 * @return void
	 */
	public function system($data)
	{
		if($this->level(OUTPUT_DEBUG))
		{
			$this->output('[system] ' . $data);
		}
	}

	/**
	 * Method called when a system event is triggered or occurs in Failnet
	 * @param string $data - The data to display
	 * @return void
	 */
	public function event($data)
	{
		if($this->level(OUTPUT_DEBUG_FULL))
		{
			$this->output('[event] ' . $data);
		}
	}

	/**
	 * Method being called on a PHP notice being thrown
	 * @param string $data - The data to display
	 * @return void
	 */
	public function notice($data)
	{
		if($this->level(OUTPUT_DEBUG))
		{
			$this->output('[php notice] ' . $data);
		}
	}

	/**
	 * Method being called on a PHP warning being thrown
	 * @param string $data - The data to display
	 * @return void
	 */
	public function warning($data)
	{
		if($this->level(OUTPUT_DEBUG))
		{
			$this->output('[php warning] ' . $data);
		}
	}

	/**
	 * Method being called on a PHP error being thrown
	 * @return void
	 */
	public function error($data)
	{
		if($this->level(OUTPUT_DEBUG))
		{
			$this->output('[php error] ' . $data);
		}
	}

	/**
	 * Method being called on debug information being output in Failnet
	 * @return void
	 */
	public function debug($data)
	{
		if($this->level(OUTPUT_DEBUG_FULL))
		{
			$this->output('[debug] ' . $data);
		}
	}

	/**
	 * Method being called on raw IRC protocol information being output in Failnet
	 * @return void
	 */
	public function raw($data)
	{
		if($this->level(OUTPUT_RAW))
		{
			$this->output('[SOCKET] ' . $data);
		}
	}
}
