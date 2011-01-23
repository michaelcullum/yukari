<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     Yukari
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2011 -- Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Yukari;

/**
 * Yukari - Kernel class,
 *      Used as the static master class that will provides easy access to the Yukari environment.
 *
 *
 * @category    Yukari
 * @package     Yukari
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
abstract class Kernel
{
	/**
	 * @var Yukari\Environment - The environment object
	 */
	protected static $environment;

	/**
	 * Stores the environment object
	 * @param Yukari\Environment $environment - The environment object.
	 * @return void
	 */
	public static function setEnvironment(Yukari\Environment $environment)
	{
		/* @var Yukari\Environment */
		self::$environment = $environment;
	}

	/**
	 * Grab the environment object, in order to make changes to global settings, or interact with loaded objects more directly
	 * @return Yukari\Environment - The environment object.
	 */
	public static function getEnvironment()
	{
		return self::$environment;
	}

	/**
	 * Get a loaded object from the environment
	 * @param mixed $object - The object's location and name.  Either an array of format array('type'=>'objecttype','name'=>'objectname'), or a string of format 'objecttype.objectname'
	 * @return object - The desired object.
	 */
	public static function getObject($object)
	{
		return self::$environment->getObject($object);
	}

	/**
	 * Get configuration options from the environment
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
