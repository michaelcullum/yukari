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

namespace Failnet\Language;
use Failnet\Bot as Bot;
use Failnet\Lib as Lib;

/**
 * Failnet - Language file compiler class,
 * 	    Collects and compiles language packages for Failnet.
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
	 * Constructor
	 * @param string $compile_path - The compile path to output to.
	 * @param mixed $import_paths - The import path(s) to load the language packages to be compiled from.
	 * @return void
	 */
	public function __construct($compile_path = '', $import_paths = '')
	{
		if($compile_path == '')
			$compile_path = FAILNET . 'data/language/';
		if($import_paths == '')
			$import_paths = array(FAILNET . 'addons/Language/Package/', FAILNET . 'src/Language/Package/');

		if(!is_array($import_paths))
			$import_paths = array($import_paths);

		$this->setCompilePath($compile_path);

		foreach($import_paths as $import_path)
		{
			$this->setImportPath($import_path);
		}
	}

	/**
	 * Set the compiler output path.
	 * @param string $path - The path to output the compiled language package data to.
	 * @return void
	 *
	 * @throws Failnet\Language\CompilerException
	 */
	public function setCompilePath($path)
	{
		// Verify that the path we are given here is an honest one.
		if(!file_exists($path) || !is_dir($path))
			throw new CompilerException(sprintf('Language compiler output path "%1$s" does not exist or is not a directory', $path), CompilerException::ERR_COMPILER_OUTPUT_PATH_MISSING);

		if(!is_writeable($path))
			throw new CompilerException(sprintf('Language compiler output path "%1$s" is unwriteable', $path), CompilerException::ERR_COMPILER_OUTPUT_PATH_UNWRITEABLE);

		$this->compile_path = $path;
	}

	/**
	 * Set a compiler import path
	 * @param string $path - The path to import language package files from.
	 * @return void
	 *
	 * @throws Failnet\Language\CompilerException
	 */
	public function setImportPath($path)
	{
		// Verify that the path we are given here is an honest one.
		if(!file_exists($path) || !is_dir($path))
			throw new CompilerException(sprintf('Language compiler import path "%1$s" does not exist or is not a directory', $path), CompilerException::ERR_COMPILER_IMPORT_PATH_MISSING);

		$this->import_paths[] = $path;
	}

	public function compile()
	{
		// asdf
		// store as {$locale}_{$package_name}.json
		// e.g. en-US_Core.json, fr-FR_SomeAddonPackageNameHere.json
	}

	public function compilePackages($locale)
	{
		// asdf
	}

	public function getLocales()
	{
		// asdf
	}

	public function getPackages($locale)
	{
		// asdf
	}
}
