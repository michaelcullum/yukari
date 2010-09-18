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
 * Failnet - Autoloading class,
 * 	    Handles automatic loading of class files based on their names.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
class Autoload
{
	/**
	 * @var array - The paths that Failnet will attempt to load class files from.
	 */
	private $paths = array();

	/**
	 * Constructor
	 * @return void
	 */
	public function __construct()
	{
		$this->paths = array(
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
		$name = $this->cleanName($class);

		foreach($this->paths as $path)
		{
			if(file_exists($path . $name . '.php'))
			{
				require $path . $name . '.php';
				if(!class_exists($class))
					throw new AutoloadException(sprintf('Invalid class contained within file %1$s', $path . $name . '.php'), AutoloadException::ERR_AUTOLOAD_CLASS_INVALID);
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
	public function getNamespaces($path, $strip = '', $prefix = '')
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
	public function setPath($include_path)
	{
		$this->paths[] = $include_path;
	}

	/**
	 * Checks to see whether or not the class file we're looking for exists (and also checks every loading dir)
	 * @param string $class - The class file we're looking for.
	 * @return boolean - Whether or not the source file we're looking for exists
	 */
	public function fileExists($class)
	{
		$name = $this->cleanName($class);

		foreach($this->paths as $path)
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
	public function cleanName($class)
	{
		$class = ($class[0] == '\\') ? substr($class, 1) : $class;
		$class = (substr($class, 0, 7) == 'Failnet') ? substr($class, 7) : $class;
		return str_replace('\\', '/', $class);
	}
}
