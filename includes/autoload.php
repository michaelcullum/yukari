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

namespace Failnet;

/**
 * Failnet - Autoloading class,
 * 		Handles automatic loading of class files based on their names.
 *
 *
 * @category	Failnet
 * @package		core
 * @author		Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Autoload extends Common
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
	public function __construct()
	{
		self::$paths = array(
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
		$name = basename($class);

		// Drop the Failnet base namespace if it is there, and replace any backslashes with slashes.
		// If you don't like it, stuff it.
		$name = str_replace('\\', '/', ((substr($name, 0, 7) == 'Failnet') ? substr($name, 7) : $name));

		foreach(self::$paths as $path)
		{
			if(file_exists($path . $name . '.php'))
			{
				require $path . $name . '.php';
				if(!class_exists($class))
					throw new failnet_exception(failnet_exception::ERR_AUTOLOAD_CLASS_INVALID, $path . $name . '.php');
				return;
			}
		}
		// Need a new solution, to handle stacking of Autoloaders.
		//throw new failnet_exception(failnet_exception::ERR_AUTOLOAD_NO_FILE, $class);
	}

	/**
	 * A quick method to allow adding more include paths to the autoloader.
	 * @param string $include_path - The include path to add to the autoloader
	 * @return void
	 */
	public static function setPath($include_path)
	{
		self::$paths[] = dirname(FAILNET_ROOT . $include_path);
	}

	/**
	 * Checks to see whether or not the class file we're looking for exists (and also checks every loading dir)
	 * @param string $class - The class file we're looking for.
	 * @return boolean - Whether or not the source file we're looking for exists
	 */
	public static function fileExists($class)
	{
		$name = basename($class);

		// Drop the Failnet base namespace if it is there, and replace any backslashes with slashes.
		// If you don't like it, stuff it.
		$name = str_replace('\\', '/', ((substr($name, 0, 7) == 'Failnet') ? substr($name, 7) : $name));

		foreach(self::$paths as $path)
		{
			if(file_exists($path . $name . '.php'))
				return true;
		}
		return false;
	}

	/**
	 * Registers an instance of this class as an autoloader.
	 * @return void
	 */
	public static function register()
	{
		spl_autoload_register(array(new self, 'loadFile'));
	}
}
