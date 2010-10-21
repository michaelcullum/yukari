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
 * @link        http://github.com/Obsidian1510/Failnet3
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
 * @link        http://github.com/Obsidian1510/Failnet3
 */
abstract class Bot
{
	/**
	 * @var Failnet\Environment - The Failnet environment object
	 */
	protected static $environment;

	/**
	 * Starts up the Bot class
	 * @param Failnet\Environment $environment - The Failnet environment object.
	 * @return void
	 */
	public static function init(Failnet\Environment $environment)
	{
		/* @var Failnet\Environment */
		self::$environment = $environment;
	}

	/**
	 * Grab the Failnet environment object, in order to make changes to global settings, or interact with loaded objects more directly
	 * @return Failnet\Environment - The Failnet environment object.
	 */
	public static function getEnvironment()
	{
		return self::$environment;
	}

	/**
	 * Get a loaded object from the Failnet environment
	 * @param mixed $object - The object's location and name.  Either an array of format array('type'=>'objecttype','name'=>'objectname'), or a string of format 'objecttype.objectname'
	 * @return object - The desired object.
	 */
	public static function getObject($object)
	{
		return self::$environment->getObject($object);
	}

	/**
	 * Get configuration options from the Failnet environment
	 * @param string $option - The option name.
	 * @param mixed $default - The default value to use if the option is not set.
	 * @param boolean $is_required - Is this option required, or can it flip to the default?
	 * @return mixed - The value of the option we're grabbing.
	 */
	public static function getOption($option, $default, $is_required = false)
	{
		return self::$environment->getOption($option, $default, $is_required);
	}
}
