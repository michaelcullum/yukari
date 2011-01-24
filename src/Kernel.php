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
	 * @const - Version stamp string for Yukari.
	 */
	const VERSION = '3.0.0-DEV';

	/**
	 * @var \Yukari\Environment - The environment object
	 */
	protected static $environment;

	/**
	 * @var \Yukari\Autoloader - The autoloader object.
	 */
	protected static $autoloader;

	/**
	 * Load the kernel up, and get the basics loaded alongside
	 * @return void
	 */
	public static function load()
	{
		// Load the Environment object
		self::setEnvironment(\Yukari\Environment::newInstance());
	}

	/**
	 * Initiate the environment object
	 * @return void
	 */
	public static function initEnvironment()
	{
		self::$environment->init();
	}

	/**
	 * Stores the environment object
	 * @param Yukari\Environment $environment - The environment object.
	 * @return void
	 */
	public static function setEnvironment(\Yukari\Environment $environment)
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
	 * Set the autoloader object in the kernel.
	 * @param \Yukari\Autoloader $autoloader - The autoloader object
	 * @return void
	 */
	public static function setAutoloader(\Yukari\Autoloader $autoloader)
	{
		self::$autoloader = $autoloader;
	}

	/**
	 * Get the current Docile autoloader object stored in the kernel.
	 * @return \Docile\Autoloader - The Docile autoloader object
	 */
	public static function getAutoloader()
	{
		return self::$autoloader;
	}

	/**
	 * Get an object that is currently being stored in the kernel.
	 * @param string $slot - The slot to look in.
	 * @return mixed - NULL if the slot specified is unused, or the object present in the slot specified.
	 */
	public static function get($slot)
	{
		return self::$environment->getObject($slot);
	}

	/**
	 * Store an object in the kernel.
	 * @param string $slot - The slot to store the object in.
	 * @param object $object - The object to store.
	 * @return object - The object just set.
	 *
	 * @throws Exception
	 */
	public static function set($slot, $object)
	{
		return self::$environment->setObject($slot, $object);
	}

	/**
	 * Get a specified configuration setting from the kernel.
	 * @param string $slot - The configuration setting's slot name.
	 * @return mixed - NULL if the slot specified is unused, or the configuration setting we wanted.
	 */
	public static function getConfig($slot)
	{
		return self::$environment->getConfig($slot);
	}

	/**
	 * Set a configuration setting in the kernel.
	 * @param string $slot - The configuration setting's slot name.
	 * @param mixed $value - The configuration value to set.
	 * @return void
	 */
	public static function setConfig($slot, $value)
	{
		return self::$environment->setConfig($slot, $value);
	}

	/**
	 * Import an array of configuration options into the kernel.
	 * @param array $config_array - The array of options to import.
	 * @return void
	 */
	public static function importConfig(array $config_array)
	{
		return self::$environment->importConfig($config_array);
	}
}
