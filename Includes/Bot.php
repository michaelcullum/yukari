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
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
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
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
abstract class Bot
{
	/**
	 * @var array - The core objects, which will also include the core class.
	 */
	protected static $core = array();

	/**
	 * @var array - Array of loaded node objects
	 */
	protected static $nodes = array();

	/**
	 * @var array - Array of loaded cron objects
	 */
	protected static $cron = array();

	/**
	 * @var array - Array of loaded plugins
	 */
	protected static $plugins = array();

	/**
	 * @var array - Array of various loaded objects
	 */
	protected static $objects = array();

	/**
	 * @var array - Array of hook data
	 */
	protected static $hooks = array();

	/**
	 * Get an object for whatever purpose
	 * @param mixed $object - The object's location and name.  Either an array of format array('type'=>'objecttype','name'=>'objectname'), or a string of format 'objecttype.objectname'
	 * @return object - The desired object.
	 *
	 * @throws BotException
	 */
	public static function getObject($object)
	{
		// If this is not an array, we need to resolve the object name for something usable.
		if(!is_array($object))
			$object = self::resolveObject($object);
		extract($object);
		if(property_exists(self, $type))
		{
			if(isset(self::$$type[$name]))
				return self::$$type[$name];
		}
		else
		{
			if(isset(self::$objects[$type][$name]))
				return self::$objects[$type][$name];
		}
		throw new BotException('The object specified was unable to be fetched.', BotException::ERR_NO_SUCH_OBJ);
	}

	/**
	 * Alias of Failnet\Bot::getObject()
	 * @see Failnet\Bot::getObject()
	 */
	public static function obj($object)
	{
		return self::getObject($object);
	}

	/**
	 * Load an object into the global class.
	 * @param mixed $object - The object's location and name.  Either an array of format array('type'=>'objecttype','name'=>'objectname'), or a string of format 'objecttype.objectname'
	 * @param mixed $value - The object to load.
	 * @return void
	 */
	public static function setObject($object, $value)
	{
		if(!is_array($object))
			$object = self::resolveObject($object);
		extract($object);
		if(property_exists(self, $type))
		{
			if(isset(self::$$type[$name]))
				self::$$type[$name] = $value;
		}
		else
		{
			if(isset(self::$objects[$type][$name]))
				self::$objects[$type][$name] = $value;
		}
	}

	/**
	 * Check to see if an object has been loaded or not
	 * @param mixed $object - The object's location and name.  Either an array of format array('type'=>'objecttype','name'=>'objectname'), or a string of format 'objecttype.objectname'
	 * @return boolean - Do we have this object?
	 */
	public static function checkObjectLoaded($object)
	{
		if(!is_array($object))
			$object = self::resolveObject($object);
		extract($object);
		if(property_exists(self, $type))
			return isset(self::$$type[$name]);
		return isset(self::$objects[$type][$name]);
	}

	/**
	 * Resolves an object's name
	 * @param string $object - The object's name we want to resolve into a workable array
	 * @return array - The resolved name location for the object.
	 */
	protected static function resolveObject($object)
	{
		$object = explode('.', $object, 1);
		$return = array(
			'name' => isset($object[1]) ? $object[1] : $object[0],
			'type' => isset($object[1]) ? $object[0] : 'core',
		);
		return $return;
	}

	/**
	 * Grabs a language entry from the language core system
	 * @note uses virtual method arguments with func_get_args(), first arg is the key to grab, additional args are used in conjunction with vsprintf()
	 * @return mixed - Whatever Failnet\Language\getEntry() returns
	 * @throws Failnet\Exception
	 */
	public static function lang()
	{
		if(func_num_args() > 1)
		{
			$args = func_get_args();
			$key = array_shift($args);
			return Bot::core('lang')->getEntry(strtoupper($key), $args);
		}
		elseif(func_num_args() === 1)
		{
			return Bot::core('lang')->getEntry(strtoupper(func_get_args(0)));
		}

		// Okay, someone was being stupid and didn't pass any parameters.
		throw new Exception(ex(Exception::ERR_LANGUAGE_CORE_NO_PARAMS));
	}

// @todo move hook storage stuff to own object

	/**
	 * Register a hook function to be called before
	 * @param array $hooked_method_call - The callback info for the method we're hooking onto.
	 * @param mixed $hook_call - The function/method to hook on top of the method we're hooking.
	 * @param constant $hook_type - The type of hook we're using.
	 * @return boolean - Were we successful?
	 * @throws Failnet\Exception
	 */
	public static function registerHook($hooked_method_class, $hooked_method_name, $hook_call, $hook_type = HOOK_NULL)
	{
		// We're deliberately ignoring HOOK_NULL here.
		if(!in_array($hook_call, array(HOOK_STACK, HOOK_OVERRIDE)))
			throw new Exception(ex(Exception::ERR_REGISTER_HOOK_BAD_HOOK_TYPE)); // @todo -> HookException

		// Check for unsupported classes
		if(substr($hooked_method_class, 0, 8) != '\\Failnet')
			throw new Exception(ex(Exception::ERR_REGISTER_HOOK_BAD_CLASS, array($hooked_method_class))); // @todo HookException

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
