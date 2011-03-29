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
	 * DO NOT _EVER_ CHANGE THESE, FOR THE SAKE OF HUMANITY.
	 * @link http://xkcd.com/534/
	 */
	const CAN_BECOME_SKYNET = false;
	const COST_TO_BECOME_SKYNET = 999999999;

	/**
	 * @var \Yukari\Environment - The environment object
	 */
	protected static $environment;

	/**
	 * @var \OpenFlame\Framework\Autoloader - The autoloader object.
	 */
	protected static $autoloader;

	/**
	 * @var \OpenFlame\Framework\Event\Dispatcher - The event dispatcher object.
	 */
	protected static $dispatcher;

	/**
	 * @var integer - This bot's build number.
	 */
	protected static $build_number;

	/**
	 * Load the kernel up, and get the basics loaded alongside
	 * @return void
	 */
	public static function load()
	{
		// Grab the build number we're running under.
		self::$build_number = (file_exists(\Yukari\ROOT_PATH . '/VERSION')) ? file_get_contents(\Yukari\ROOT_PATH . '/VERSION') : false;

		// Load the Environment object
		self::setEnvironment(\Yukari\Environment::newInstance());

		// Setup the autoloader.
		self::setAutoloader(\OpenFlame\Framework\Autoloader::register());
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
	 * @param \Yukari\Environment $environment - The environment object.
	 * @return void
	 */
	public static function setEnvironment(\Yukari\Environment $environment)
	{
		self::$environment = $environment;
	}

	/**
	 * Grab the environment object, in order to make changes to global settings, or interact with loaded objects more directly
	 * @return \Yukari\Environment - The environment object.
	 */
	public static function getEnvironment()
	{
		return self::$environment;
	}

	/**
	 * Set the autoloader object in the kernel.
	 * @param \OpenFlame\Framework\Autoloader $autoloader - The autoloader object
	 * @return void
	 */
	public static function setAutoloader(\OpenFlame\Framework\Autoloader $autoloader)
	{
		self::$autoloader = $autoloader;
	}

	/**
	 * Get the current autoloader object stored in the kernel.
	 * @return \OpenFlame\Framework\Autoloader - The autoloader object
	 */
	public static function getAutoloader()
	{
		return self::$autoloader;
	}

	/**
	 * Stores the event dispatcher object
	 * @param \OpenFlame\Framework\Event\Dispatcher $dispatcher - The event dispatcher object.
	 * @return void
	 */
	public static function setDispatcher(\OpenFlame\Framework\Event\Dispatcher $dispatcher)
	{
		self::$dispatcher = $dispatcher;
	}

	/**
	 * Grab the event dispatcher object.
	 * @return \OpenFlame\Framework\Event\Dispatcher - The event dispatcher object.
	 */
	public static function getDispatcher()
	{
		return self::$dispatcher;
	}

	/**
	 * Get the build number for this version of Yukari
	 * @return string - The build string for this version of Yukari.
	 */
	public static function getBuildNumber()
	{
		return (self::$build_number !== false) ? sprintf('build_%d', self::$build_number) : 'build_DEV';
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
