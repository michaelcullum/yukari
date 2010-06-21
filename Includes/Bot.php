<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2010 -- Damian Bushong
 * @license     GNU General Public License, Version 3
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Failnet;

/**
 * Failnet - Master class,
 *      Used as the master static class that will contain all node objects, core objects, etc.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     GNU General Public License, Version 3
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
abstract class Bot
{
	/**
	 * @var array - The core objects, which will also include the core class.
	 */
	private static $core = array();

	/**
	 * @var array - Array of loaded node objects
	 */
	private static $nodes = array();

	/**
	 * @var array - Array of loaded cron objects
	 */
	private static $cron = array();

	/**
	 * @var array - Array of loaded plugins
	 */
	private static $plugins = array();

	/**
	 * @var array - Array of hook data
	 */
	protected static $hooks = array();

	/**
	 * @var array - Array of all $argv arguments passed to the script, properly parsed.
	 */
	protected static $args = array();

	/**
	 * Grab the core object.
	 * @param string $core_name - The name of the core object that we want, or an empty string if we want THE core.
	 * @return \Failnet\Base - The desired core object if present.
	 * @throws Failnet\Exception
	 */
	public static function core($core_name = '')
	{
		if(empty($core_name))
			return self::$core['core'];
		if(self::checkCoreLoaded($core_name))
			return self::$core[$core_name];
		throw new Exception(ex(Exception::ERR_NO_SUCH_CORE_OBJ));
	}

	/**
	 * Grab a node object.
	 * @param string $node_name - The name of the node object that we want.
	 * @return \Failnet\Base - The desired node object if present, or void if no such object.
	 * @throws Failnet\Exception
	 */
	public static function node($node_name)
	{
		if(!self::checkNodeLoaded($node_name))
			throw new Exception(ex(Exception::ERR_NO_SUCH_NODE_OBJ));
		return self::$nodes[$node_name];
	}

	/**
	 * Grab a cron object.
	 * @param string $cron_name - The name of the cron object that we want.
	 * @return \Failnet\Cron\Common - The desired cron object if present, or void if no such object.
	 * @throws Failnet\Exception
	 */
	public static function cron($cron_name)
	{
		if(empty($cron_name))
			return self::$cron['core'];
		if(!self::checkCronLoaded($cron_name))
			throw new Exception(ex(Exception::ERR_NO_SUCH_CRON_OBJ));
		return self::$cron[$cron_name];
	}

	/**
	 * Grab a plugin object.
	 * @param string $plugin_name - The name of the plugin object that we want.
	 * @return Failnet\Plugin\Common - The desired plugin object if present.
	 * @throws Failnet\Exception
	 */
	public static function plugin($plugin_name)
	{
		if(!self::checkPluginLoaded($plugin_name))
			throw new Exception(ex(Exception::ERR_NO_SUCH_PLUGIN_OBJ));
		return self::$plugins[$plugin_name];
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
	 * Create a new core object.
	 * @param string $cron_name - The name of the cron slot to load into.
	 * @param string $cron_class - The name of the class to load.
	 * @return void
	 */
	public static function setCron($cron_name, $cron_class)
	{
		self::$cron[$cron_name] = new $cron_class();
	}

	/**
	 * Create a new core object.
	 * @param string $cron_name - The name of the cron slot to load into.
	 * @param string $cron_class - The name of the class to load.
	 * @return void
	 */
	public static function setPlugin($plugin_name, $plugin_class)
	{
		self::$plugins[$plugin_name] = new $plugin_class();
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
	 * Checks to see if the specified cron slot has been occupied
	 * @param string $cron_name - The name of the cron slot to check
	 * @return boolean - Whether or not a cron object has been loaded yet into the specified slot
	 */
	public static function checkCronLoaded($cron_name)
	{
		return isset(self::$cron[$cron_name]);
	}

	/**
	 * Checks to see if the specified cron slot has been occupied
	 * @param string $plugin_name - The name of the plugin slot to check
	 * @return boolean - Whether or not a plugin object has been loaded yet into the specified slot
	 */
	public static function checkPluginLoaded($plugin_name)
	{
		return isset(self::$plugins[$plugin_name]);
	}


	/**
	 * Pull a specific arg that should have been passed to the script, it was sent.
	 * @param string $arg_name - The name of the CLI arg to grab (must have been present in $_SERVER['argv'])
	 * @return mixed - NULL if no such arg, the arg if present.
	 */
	public static function arg($arg_name)
	{
		if(isset(self::$args[$arg_name]))
			return self::$args[$arg_name];
		return NULL;
	}

	/**
	 * Load up the CLI args and parse them.
	 * @param array $args - An array of CLI args to load and parse
	 * @return void
	 *
	 * @copyright   (c) 2010 Sam Thompson
	 * @author      Sam Thompson
	 * @license     GNU General Public License, Version 3
	 * @note        This code generously provided by a friend of mine, Sam Thompson.  Kudos!
	 */
	public static function loadArgs(array $args)
	{
		foreach($args as $i => $val)
		{
			if($val[0] === '-')
			{
				if($val[1] === '-')
				{
					$separator = strpos($val, '=');
					if($separator === false)
					{
						self::$args[substr($val, 2, $separator - 2)] = substr($val, $separator + 1);
					}
					else
					{
						self::$args[substr($val, 2)] = true;
					}
				}
				else
				{
					self::$args[substr($val, 1)] = true;
				}
			}
		}
	}


	/**
	 * Register a hook function to be called before
	 * @param array $hooked_method_call - The callback info for the method we're hooking onto.
	 * @param mixed $hook_call - The function/method to hook on top of the method we're hooking.
	 * @param constant $hook_type - The type of hook we're using.
	 * @return boolean - Were we successful?
	 * @throws failnet_exception
	 */
	public static function registerHook($hooked_method_class, $hooked_method_name, $hook_call, $hook_type = HOOK_NULL)
	{
		// We're deliberately ignoring HOOK_NULL here.
		if(!in_array($hook_call, array(HOOK_STACK, HOOK_OVERRIDE)))
			throw new Exception(ex(Exception::ERR_REGISTER_HOOK_BAD_HOOK_TYPE));

		// Check for unsupported classes
		if(substr($hooked_method_class, 0, 8) != '\\Failnet')
			throw new Exception(ex(Exception::ERR_REGISTER_HOOK_BAD_CLASS, array($hooked_method_class)));

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
	}

	/**
	 * Checks to see if any hooks have been assigned to a designated class/method, and returns their info.
	 * @param string $hooked_method_class - The name of the class to check a method of for hooks
	 * @param string $hooked_method_name - The name of the previously specified class's method to check for hooks
	 * @return mixed - Returns either false if there's no such hooks associated, or returns the array containing that method's hook data.
	 */
	public static function retrieveHook($hooked_method_class, $hooked_method_name)
	{
		if(!isset(self::$hooks[$hooked_method_class][$hooked_method_name]))
			return false;
		return self::$hooks[$hooked_method_class][$hooked_method_name];
	}
}
