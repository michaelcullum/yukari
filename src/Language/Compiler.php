<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     language
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

namespace Yukari\Language;

/**
 * Yukari - Language file compiler class,
 * 	    Collects and compiles language packages for Yukari.
 *
 *
 * @category    Yukari
 * @package     language
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Compiler
{
	/**
	 * @var string - The path to write the compiled language files to.
	 */
	protected $compile_path = '';

	/**
	 * @var array - The paths to import language packages from.
	 */
	protected $import_paths = array();

	/**
	 * @var array - The list of packages to compile.
	 */
	protected $packages = array();

	/**
	 * Constructor
	 * @param string $compile_path - The compile path to output to.
	 * @param mixed $import_paths - The import path(s) to load the language packages to be compiled from.
	 * @return void
	 */
	public function __construct($compile_path = '', $import_paths = '')
	{
		if($compile_path == '')
			$compile_path = YUKARI . '/data/language/';
		if($import_paths == '')
			$import_paths = array(YUKARI . '/addons/Language/Package/', Yukari\ROOT_PATH . 'src/Language/Package/');

		if(!is_array($import_paths))
			$import_paths = array($import_paths);

		$this->setCompilePath($compile_path);

		foreach($import_paths as $import_path)
			$this->setImportPath($import_path);
	}

	/**
	 * Set the compiler output path.
	 * @param string $path - The path to output the compiled language package data to.
	 * @return \Yukari\Language\Compiler - Provides a fluent interface.
	 *
	 * @throws \RuntimeException
	 */
	public function setCompilePath($path)
	{
		// Verify that the path we are given here is an honest one.
		if(!file_exists($path) || !is_dir($path))
			throw new \RuntimeException(sprintf('Language compiler output path "%1$s" does not exist or is not a directory', $path));

		if(!is_writeable($path))
			throw new \RuntimeException(sprintf('Language compiler output path "%1$s" is unwriteable', $path));

		$this->compile_path = rtrim($path, '/') . '/';
		return $this;
	}

	/**
	 * Set a compiler import path
	 * @param string $path - The path to import language package files from.
	 * @return \Yukari\Language\Compiler - Provides a fluent interface.
	 *
	 * @throws \RuntimeException
	 */
	public function setImportPath($path)
	{
		// Verify that the path we are given here is an honest one.
		if(!file_exists($path) || !is_dir($path))
			throw new \RuntimeException(sprintf('Language compiler import path "%s" does not exist or is not a directory', $path));

		$this->import_paths[] = rtrim($path, '/') . '/';

		return $this;
	}

	/**
	 * Register a new package to be compiled.
	 * @param string $package - The name of the package to compile.
	 * @return \Yukari\Language\Compiler - Provides a fluent interface.
	 */
	public function registerPackage($package)
	{
		if(!in_array($package, $this->packages))
			$this->packages[] = $package;

		return $this;
	}

	/**
	 * Compile a given language package and store as a JSON array, in a text file.
	 * @param string $locale - The locale of the package to compile.
	 * @param string $package - The name of the package to compile.
	 * @return \Yukari\Language\Compiler - Provides a fluent interface.
	 */
	public function compile($locale, $package)
	{
		$class = "\\Yukari\\Language\\Package\\{$locale}\\{$package}";
		$object = new $class();
		file_put_contents($this->compile_path . "{$locale}_{$package}.json", $object->toJSON(), LOCK_EX);

		return $this;
	}

	/**
	 * Compile all the registered packages for a given locale.
	 * @param string - The locale to compile our langauge packages for.
	 * @return \Yukari\Language\Compiler - Provides a fluent interface.
	 */
	public function compilePackages($locale)
	{
		foreach($this->packages as $package)
			$this->compile($locale, $package);

		return $this;
	}
}
