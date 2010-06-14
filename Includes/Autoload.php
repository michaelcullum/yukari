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
 * @author      Failnet Project
 * @copyright   (c) 2009 - 2010 -- Failnet Project
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
 * Failnet - Autoloading class,
 * 	    Handles automatic loading of class files based on their names.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Failnet Project
 * @license     GNU General Public License, Version 3
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Autoload extends Base
{
	/**
	 * @var array - The paths that Failnet will attempt to load class files from.
	 */
	private static $paths = array();

	/**
	 * @ignore
	 */
	public function __construct()
	{
		self::$paths = array(
			FAILNET_ROOT . 'Includes/',
			FAILNET_ROOT . 'Addons/Autoload/',
			FAILNET_ROOT . 'Addons/',
		);
	}

	/**
	 * Autoload callback for loading class files.
	 * @param string $class - Class to load
	 * @return void
	 */
	public function loadFile($class)
	{
		$name = self::cleanName($class);

		foreach(self::$paths as $path)
		{
			if(file_exists($path . $name . '.php'))
			{
				require $path . $name . '.php';
				if(!class_exists($class))
					throw new Exception(ex(Exception::ERR_AUTOLOAD_CLASS_INVALID, $path . $name . '.php'));
				return;
			}
		}
		return false;
	}

	/**
	 * Scan a directory for files that we would want to autoload
	 * @param string $path - The path to scan
	 * @param string $strip - Anything extra to strip out of the path when generating the namespace
	 * @param string $prefix - A namespace prefix to use, if we need one
	 * @return array - An array of class names to autoload.
	 */
	function getNamespaces($path, $strip = '', $prefix = '')
	{
		$files = scandir($path);
		foreach($files as $file)
		{
			if($file[0] == '.' || substr(strrchr($file, '.'), 1) != 'php')
				continue;

			$prefix = (!$prefix) ? 'Failnet\\' . str_replace('/', '\\', substr($path, strlen(FAILNET_ROOT . $strip) + 1)) : $prefix;
			$return[] = $prefix . (substr($prefix, -1, 1) != '\\' ? '\\' : '') . ucfirst(substr($file, 0, strrpos($file, '.')));
		}
		return $return;
	}

	/**
	 * A quick method to allow adding more include paths to the autoloader.
	 * @param string $include_path - The include path to add to the autoloader
	 * @return void
	 */
	public static function setPath($include_path)
	{
		self::$paths[] = FAILNET_ROOT . $include_path;
	}

	/**
	 * Checks to see whether or not the class file we're looking for exists (and also checks every loading dir)
	 * @param string $class - The class file we're looking for.
	 * @return boolean - Whether or not the source file we're looking for exists
	 */
	public static function fileExists($class)
	{
		$name = self::cleanName($class);

		foreach(self::$paths as $path)
		{
			if(file_exists($path . $name . '.php'))
				return true;
		}
		return false;
	}

	/**
	 * Drop the Failnet base namespace if it is there, and replace any backslashes with slashes.
	 * @param string $class_name - The name of the class to spit-polish.
	 * @return string - The cleaned class name.
	 */
	public static function cleanName($class)
	{
		$class = ($class[0] == '\\') ? substr($class, 1) : $class;
		$class = (substr($class, 0, 7) == 'Failnet') ? substr($class, 7) : $class;
		return str_replace('\\', '/', $class);
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
