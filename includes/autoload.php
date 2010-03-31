<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * @version:	2.1.0 DEV
 * @copyright:	(c) 2009 - 2010 -- Failnet Project
 * @license:	http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
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
 * Failnet - Autoloading class,
 * 		Handles automatic loading of class files based on their names.
 *
 *
 * @package core
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_autoload extends failnet_common
{
	/**
	 * @var array - The paths that Failnet will attempt to load class files from.
	 */
	private static $paths = array();

	/**
	 * Specialized init function to allow class construction to be easier.
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	public function init()
	{
		$this->paths = array(
			FAILNET_ROOT . 'addons/autoload/',
			FAILNET_ROOT . 'includes/',
			FAILNET_ROOT . 'addons/',
		);
	}

	/**
	 * Autoload callback for loading class files.
	 * @param string $class - Class to load
	 * @return void
	 */
	public function loadFile($class)
	{
		// Begin by cleaning the class name of any possible ../. hacks
		$name = basename(sanitize_filepath($class));

		// Now, drop the failnet_ prefix if it is there, and replace any underscores with slashes.
		$name = str_replace('_', '/', ((substr($name, 0, 8) == 'failnet_') ? substr($name, 8) : $name));

		$found = false;
		foreach(self::$paths as $path)
		{
			if(file_exists($path . $name . '.php'))
			{
				require $path . $name . '.php';
				if(!class_exists($class))
				{
					throw_fatal('Invalid class contained within file ' . $path . $name . '.php');
				}
				$found = true;
				return;
			}
		}
		throw_fatal('No class file found for class named ' . $class . ', expecting ' . $path . $name . '.php for filepath in specified include directories');
	}

	/**
	 * A quick method to allow adding more include paths to the autoloader.
	 * @param string $include_path - The include path to add to the autoloader
	 * @return void
	 */
	public static function setPath($include_path)
	{
		self::$paths[] = sanitize_filepath(FAILNET_ROOT . $include_path);
	}

	/**
	 * Checks to see whether or not the class file we're looking for exists (and also checks every loading dir)
	 * @param string $class - The class file we're looking for.
	 * @return boolean - Whether or not the source file we're looking for exists
	 */
	public static function fileExists($class)
	{
		// Begin by cleaning the class name of any possible ../. hacks
		$name = basename(sanitize_filepath($class));

		// Now, drop the failnet_ prefix if it is there, and replace any underscores with slashes.
		$name = str_replace('_', '/', ((substr($name, 0, 8) == 'failnet_') ? substr($name, 8) : $name));

		foreach(self::$paths as $path)
		{
			if(file_exists($path . $name . '.php'))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Registers an instance of this class as an autoloader.
	 * @return void
	 */
	public static function register()
	{
		spl_autoload_register(array(new self, 'load'));
	}
}
