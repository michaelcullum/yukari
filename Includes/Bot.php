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
	 * @var array - Array of loaded configuration options
	 */
	protected static $options = array();

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
}
