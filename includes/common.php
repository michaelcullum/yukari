<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version		2.1.0 DEV
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @copyright	(c) 2009 - 2010 -- Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
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
 * Failnet - Master class,
 * 		Used as the master static class that will contain all node objects, core objects, etc.
 *
 *
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
abstract class failnet
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
	 * @throws failnet_exception
	 */
	public static function core($core_name = '')
	{
		if(empty($core_name))
			return self::$core['core'];
		if(self::checkCoreLoaded($core_name))
			return self::$core[$core_name];
		throw new failnet_exception(failnet_exception::ERR_NO_SUCH_CORE_OBJ, $core_name);
	}

	/**
	 * Grab a node object.
	 * @param string $node_name - The name of the node object that we want.
	 * @return mixed - Either the desired node object, or NULL if no such object.
	 * @throws failnet_exception
	 */
	public static function node($node_name)
	{
		if(!self::checkNodeLoaded($node_name))
			throw new failnet_exception(failnet_exception::ERR_NO_SUCH_NODE_OBJ, $node_name);
		return self::$nodes[$node_name];
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

	/**
	 * Checks to see if the specified core slot has been occupied
	 * @param string $core_name - The name of the core slot to check
	 * @return boolean - Whether or not a core object has been loaded yet into the specified slot
	 */
	public static function checkCoreLoaded($core_name)
	{
		return isset(self::$core[$core_name]);
	}

	/**
	 * Checks to see if the specified node slot has been occupied
	 * @param string $node_name - The name of the node slot to check
	 * @return boolean - Whether or not a node object has been loaded yet into the specified slot
	 */
	public static function checkNodeLoaded($node_name)
	{
		return isset(self::$nodes[$core_name]);
	}


	/**
	 * Register a hook function to be called before
	 * @param array $hooked_method_call - The callback info for the method we're hooking onto.
	 * @param mixed $hook_call - The function/method to hook on top of the method we're hooking.
	 * @param constant $hook_type - The type of hook we're using.
	 * @return boolean - Were we successful?
	 */
	public function registerHook($hooked_method_class, $hooked_method_name, $hook_call, $hook_type = HOOK_NULL)
	{
		// We're deliberately ignoring HOOK_NULL here.
		if(!in_array($hook_call, array(HOOK_STACK, HOOK_OVERRIDE)))
			// throw an exception instead
			return false;

		// Check for unsupported classes
		if(substr($hooked_method_class, 0, 8) != 'failnet_')
			// throw an exception instead
			return false;

		/**
		 * Hooks are placed into the hook info array using the following array structure:
		 *
		 <code>
			self::$hooks[$hooked_method_class][$hooked_method_name] = array(
				array(
					'hook_call'		=> $hook_call,
					'type'			=> HOOK_STACK,
				),
				array(
					'hook_call'		=> $hook_call,
					'type'			=> HOOK_OVERRIDE,
				),
			);
		 </code>
		 *
		 */

		/**
		 * At some point in the future, we may want to check to see if the method we are hooking onto exists,
		 * but for now we will not, as the class may not yet be loaded.
		 * We'll just have to take their word for it.
		 */
		self::$hooks[$hooked_method_class][$hooked_method_name][] = array('hook_call' => $hook_call, 'type' => $hook_type);
		return true;
	}

	/**
	 * Checks to see if any hooks have been assigned to a designated class/method, and returns their info.
	 * @param string $hooked_method_class - The name of the class to check a method of for hooks
	 * @param string $hooked_method_name - The name of the previously specified class's method to check for hooks
	 * @return mixed - Returns either false if there's no such hooks associated, or returns the array containing that method's hook data.
	 */
	public function retrieveHook($hooked_method_class, $hooked_method_name)
	{
		if(!isset(self::$hooks[$hooked_method_class][$hooked_method_name]))
			return false;
		return self::$hooks[$hooked_method_class][$hooked_method_name];
	}
}

/**
 * Failnet - Base class,
 * 		Used as the base class that will handle method hooking.
 *
 *
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
abstract class failnet_base
{
	public static $__CLASS__ = __CLASS__;

	/**
	 * __call hook enabler, intercepts calls to methods and checks for hooks, then forwards the call to the actual method.
	 * @param string $name - Method name
	 * @param array $arguments - Method parameters
	 * @return void
	 */
	public function __call($name, $arguments)
	{
		if(method_exists($this, "_$name"))
		{
			$hook_ary = failnet::retrieveHook(get_class($this), $name);
			if(!empty($hook_ary))
			{
				foreach($hook_ary as $hook)
				{
					// process the hook data here
					if($hook['type'] === HOOK_OVERRIDE)
					{
						return call_user_func_array($hook['hook_call'], $arguments);
					}
					elseif($hook['type'] === HOOK_STACK)
					{
						call_user_func_array($hook['hook_call'], $arguments);
					}
				}
			}
			return call_user_func_array(array($this, "_$name"), $arguments);
		}
		else
		{
			// replace with exception
			trigger_error("Call to undefined method '$name' in class '" . get_class($this) . "'");
		}
	}

	/**
	 * __callStatic hook enabler, intercepts static calls to methods and checks for hooks, then forwards the static call to the actual method.
	 * @param string $name - Method name
	 * @param array $arguments - Method parameters
	 * @return void
	 */
	public function __callStatic($name, $arguments)
	{
		if(method_exists(static::$__CLASS__, "_$name"))
		{
			$hook_ary = failnet::retrieveHook(static::$__CLASS__, $name);
			if(!empty($hook_ary))
			{
				foreach($hook_ary as $hook)
				{
					// process the hook data here
					if($hook['type'] === HOOK_OVERRIDE)
					{
						return call_user_func_array($hook['hook_call'], $arguments);
					}
					elseif($hook['type'] === HOOK_STACK)
					{
						call_user_func_array($hook['hook_call'], $arguments);
					}
				}
			}
			return call_user_func_array(array(static::$__CLASS__, "static::_$name"), $arguments);
		}
		else
		{
			// replace with exception
			trigger_error("Call to undefined method '$name' in class '" . static::$__CLASS__ . "'");
		}
	}
}

/**
 * Failnet - Common class,
 * 		Used as the common class for most of Failnet's classes
 *
 *
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
abstract class failnet_common extends failnet_base
{
	/**
	 * Auth level constants for Failnet
	 */
	const AUTH_OWNER = 6;
	const AUTH_SUPERADMIN = 5;
	const AUTH_ADMIN = 4;
	const AUTH_TRUSTEDUSER = 3;
	const AUTH_KNOWNUSER = 2;
	const AUTH_REGISTEREDUSER = 1;
	const AUTH_UNKNOWNUSER = 0;
}
