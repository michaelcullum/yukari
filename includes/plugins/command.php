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
if(!defined('IN_FAILNET')) exit(1);

/**
 * Failnet - Plugin command class,
 * 		Used as a direct command handling class for Failnet.
 * 
 *  Heavily borrowed from Phergie.
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
abstract class failnet_plugin_command extends failnet_plugin_common
{
	/**
	 * Cache for command lookups used to confirm that methods exist and 
	 * parameter counts match
	 *
	 * @var array
	 */
	private $methods = array();

	/**
	 * Initialize the methods cache when the bot connects to the server.
	 *
	 * @return void
	 */
	public function cmd_connect()
	{
		$reflector = new ReflectionClass(get_class($this));
		foreach ($reflector->getMethods() as $method)
		{
			$name = $method->getName();
			if (strpos($name, 'call_') === 0)
			{
				$this->methods[strtolower(substr($name, 4))] = array(
					'total' => $method->getNumberOfParameters(),
					'required' => $method->getNumberOfRequiredParameters()
				);
			}
		}
	}

	/**
	 * Parses a given message and, if its format corresponds to that of a
	 * defined command, calls the handler method for that command with any
	 * provided parameters.
	 *
	 * @return void
	 */
	public function cmd_privmsg()
	{
		// Get the content of the message
		$msg = trim($this->event->get_arg('text'));

		if (strpos($msg, $this->failnet->nick) !== 0)
		{
			return;
		}
		else
		{
			$msg = substr($msg, strlen($this->failnet->nick));
		}

		// Separate the command and arguments
		$parsed = preg_split('/\s+/', $msg, 2);
		$cmd = strtolower(array_shift($parsed));
		$args = count($parsed) ? array_shift($parsed) : '';
		$method = 'call_cmd_' . strtolower($cmd); 

		// Check to ensure the command exists
		if (empty($this->methods[$cmd]))
			return;

		// If no arguments are passed...
		if (empty($args))
		{
			// If the method requires no arguments, call it
			if (empty($this->methods[$cmd]['required']))
				$this->$method();

		// If arguments are passed...
		}
		else
		{
			// Parse the arguments
			$args = preg_split('/\s+/', $args, $this->methods[$cmd]['total']);

			// If the minimum arguments are passed, call the method 
			if ($this->methods[$cmd]['required'] <= count($args))
				call_user_func_array(array($this, $method), $args);
		}
	}
}
