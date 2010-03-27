<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 2
 * Copyright:	(c) 2009 - 2010 -- Failnet Project
 * License:		GNU General Public License - Version 2
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
 */

/**
 * Failnet - Master class,
 * 		Used as the master static class that will contain all nodes, lib objects, etc.
 *
 *
 * @package core
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet
{
	/**
	 * @var array - The core objects, which will also include the core class.
	 */
	public static $core = array();

	/**
	 * @var array - Array of loaded node objects
	 */
	public static $nodes = array();

	/**
	 * @var array - Array of hook data
	 */
	public static $hooks = array();

	/**
	 * Grab the core object.
	 * @param string $core_name - The name of the core object that we want, or an empty string if we want THE core.
	 * @return mixed - Either the desired core object, or NULL if no such object.
	 */
	public static function core($core_name = '')
	{
		if(empty($core_name))
			return self::$core['core'];
		if(isset(self::$core[$core_name]))
			return self::$core[$core_name];
		return NULL;
	}

	/**
	 * Grab a node object.
	 * @param string $node_name - The name of the node object that we want.
	 * @return mixed - Either the desired node object, or NULL if no such object.
	 */
	public static function node($node_name)
	{
		if(isset(self::$nodes[$node_name]))
			return self::$nodes[$node_name];
		// @todo throw new exception, when implemented
		return NULL;
	}

	/**
	 * Create a new core object.
	 * @param string $core_name - The name of the core slot to load into.
	 * @param string $core_class - The name of the class to load.
	 * @return void
	 */
	public static function setCore($core_name, $core_class)
	{
		self::$core[$core_name] = new $core_class();
	}

	/**
	 * Create a new node object.
	 * @param string $node_name - The name of the node slot to load into.
	 * @param string $node_class - The name of the class to load.
	 * @return void
	 */
	public static function setNode($node_name, $node_class)
	{
		self::$nodes[$node_name] = new $node_class();
	}

	public function registerHook($hooked_call, $hook_call)
	{
		// some code goes here.
	}
}

abstract class failnet_base
{
	public function __call($name, array $arguments)
	{
		// @todo write the hook handling code here
	}
}

/**
 * Failnet - Base class,
 * 		Used as the common base class for all of Failnet's class files (at least the ones that need one)
 *
 *
 * @package core
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
abstract class failnet_common
{
	/**
	 * @var object failnet_core - The mothership itself.
	 */
	protected $failnet;

	/**
	 * Constants for Failnet.
	 */

	/**
	 * Auth levels for Failnet
	 */
	const AUTH_OWNER = 6;
	const AUTH_SUPERADMIN = 5;
	const AUTH_ADMIN = 4;
	const AUTH_TRUSTEDUSER = 3;
	const AUTH_KNOWNUSER = 2;
	const AUTH_REGISTEREDUSER = 1;
	const AUTH_UNKNOWNUSER = 0;

	/**
	 * Constructor method.
	 * @param object failnet_core $failnet - The Failnet core object.
	 * @return void
	 */
	public function __construct(failnet_core $failnet)
	{
		$this->failnet = $failnet;
		$this->init();
	}

	/**
	 * Handler method for class load
	 * @return void
	 */
	abstract public function init();

	/**
	 * Magic method __call, implements hook calls for specialized method hooking.
	 * @param string $name - The name of the method that is being called
	 * @param array $arguments - The arguments that are being passed to the specified method
	 * @return mixed
	 */
	public function __call($name, array $arguments)
	{
		if(!method_exists($this->failnet, $name))
		{
			trigger_error('Call to undefined method "' . $name . '" in class "' . __CLASS__ . '"', E_USER_WARNING);
		}
		else
		{
			return call_user_func_array(array($this->failnet, $name), $arguments);
		}
	}
}
