<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2010 -- Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet\Core;
use Failnet as Root;
use Failnet\Bot as Bot;
use Failnet\Lang as Lang;

/**
 * Failnet - Language class,
 * 	    Collects and provides access to all of the language entries for Failnet.
 *
 *
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Language extends Root\Hookable
{
	/**
	 * @var array - Array containing all language variables that have been loaded.
	 */
	protected $entries = array();

	/**
	 * @var array - Array of all language files that have been loaded.
	 */
	protected $files = array();

	/**
	 * @var string - The path to load language files from.
	 */
	protected $language_dir = '';

	/**
	 * Constructor
	 * @param string $language_dir - The directory to load language files from.
	 * @return void
	 */
	public function __construct($language_dir)
	{
		$this->setPath($language_dir);
	}

	/**
	 * Set the path to search for language files in
	 * @param string $language_dir - The directory to load language files from.
	 * @return void
	 */
	public function setPath($language_dir)
	{
		$this->language_dir = $language_dir;
	}

	/**
	 * Loads all present language files
	 * @return void
	 */
	public function collectEntries()
	{
		/* @var Failnet\Core\UI */
		$ui = Bot::getObject('core.ui');

		$files = scandir($this->language_dir);
		foreach($files as $file)
		{
			// ignore useless files
			if($file[0] == '.' || substr(strrchr($file, '.'), 1) != 'php')
				continue;

			// We're using a try+catch block here in case a language file fails to load
			try
			{
				$this->loadFile($this->language_dir . '/' . $file);
			}
			catch(LanguageException $e)
			{
				$ui->debug('Failed to load language file ' . substr(strrchr($file, '.'), 1)); // @todo recode, add in $e->getMessage()
			}
		}
	}

	/**
	 * Load a language file
	 * @param string $file - The full filepath of the language file to load.
	 * @return void
	 *
	 * @throws Failnet\Core\LanguageException
	 *
	 * @note This method will not allow reloading a language file
	 */
	public function loadFile($file)
	{
		/* @var Failnet\Core\UI */
		$ui = Bot::getOption('core.ui');

		$filename = substr(strrchr(basename($file), '.'), 1);
		if(in_array($filename, $this->files))
		{
			// @todo throw exception, not debug notice
			$ui->debug('ignoring call to Failnet\\Core\\Language::loadFile() - language file already loaded');
			return;
		}

		// Okay, time to include the file.  We use include on language files in case something blows up.
		if(($include = @include($file)) === false)
			throw new LanguageException(sprintf('Language file "%1$s" could not be loaded', $file), LanguageException::ERR_LANGUAGE_FILE_LOAD_FAILED);

		// Add this language file to the list of loaded language files
		$this->files[] = $filename;
		$ui->system('--- Loaded language file' . $filename);
	}

	/**
	 * Pulls a language variable from Failnet, and will vsprintf() extra strings into the language variable if desired
	 * @param string $key - The language key of the variable to fetch
	 * @param array $arguments - Any parameters that should be passed to vsprintf() if desired
	 * @return string - The desired language variable, parsed by vsprintf() if desired
	 */
	public function getEntry($key, array $arguments = array())
	{
		// If there's no such language key, we'll return an empty string
		if(!isset($this->entries[$key]))
			return $key;

		if(!empty($arguments))
			return $this->entries[$key];

		// Okay, someone's gotta be difficult.  We need to vsprintf(), yay.
		return vsprintf($this->entries[$key], $arguments);
	}

	/**
	 * Load a language variable into Failnet for future use.
	 * @param string $key - The language key to use this language variable with.
	 * @param string $value - A plain or sprintf() formatted string to use for a language variable.
	 * @return void
	 *
	 * @note This method WILL overwrite previously loaded language variables!
	 */
	public function setEntry($key, $value)
	{
		$this->entries[strtoupper($key)] = $value;
	}

	/**
	 * Load up an array of language variables
	 * @param array $entries - The array of entries to load
	 * @return void
	 *
	 * @note This method WILL overwrite previously loaded language variables!
	 */
	public function setEntries(array $entries)
	{
		foreach($entries as $key => $value)
		{
			$this->setEntry($key, $value);
		}
	}

	/**
	 * Alias of Failnet\Core\Language->getEntry()
	 * @see Failnet\Core\Language->getEntry()
	 */
	public function __invoke($key, array $arguments = array())
	{
		return $this->getEntry($key, $arguments);
	}
}

/**
 * Failnet - Subordinate exception class
 *      Extension of the Failnet exception class.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 * @note reserves 204xx error codes
 */
class LanguageException extends Root\FailnetException
{
	const ERR_LANGUAGE_FILE_LOAD_FAILED = 20400;
}
