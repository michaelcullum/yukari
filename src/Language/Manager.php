<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     language
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

namespace Failnet\Language;
use Failnet as Root;
use Failnet\Bot as Bot;
use Failnet\Lib as Lib;

/**
 * Failnet - Language manager class,
 * 	    Collects and provides access to all of the language entries for Failnet.
 *
 *
 * @category    Failnet
 * @package     language
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
class Manager
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
		$this->language_dir = rtrim($language_dir, '/\\');
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
			if($file[0] == '.' || substr(strrchr($file, '.'), 1) != 'json')
				continue;

			// We're using a try+catch block here in case a language file fails to load
			try
			{
				$this->loadFile($file);
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
	 * @throws Failnet\Language\ManagerException
	 *
	 * @note This method will not allow reloading a language file
	 */
	public function loadFile($file)
	{
		/* @var Failnet\Core\UI */
		$ui = Bot::getObject('core.ui');

		$file = basename($file);
		if(in_array($file, $this->files))
			throw new ManagerException(sprintf('Language file "%1$s" cannot be reloaded', $file), ManagerException::ERR_LANGUAGE_FILE_RELOAD_FAILED);

		// Let's try to load the language file...we use try/catch in case something goes nuclear with the JSON processing, here.
		try
		{
			$entries = Lib\JSON::decode($this->language_dir . $file);
		}
		catch(Lib\JSONException $e)
		{
			throw new ManagerException(sprintf('Language file "%1$s" could not be loaded', $file), ManagerException::ERR_LANGUAGE_FILE_LOAD_FAILED);
		}

		// Store the new language entries
		$this->setEntries($entries);

		// Add this language file to the list of loaded language files
		$this->files[] = $file;
		$ui->system('--- Loaded language file' . $file);
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
	 * Alias of Failnet\Language\Manager->getEntry()
	 * @see Failnet\Language\Manager->getEntry()
	 */
	public function __invoke($key, array $arguments = array())
	{
		return $this->getEntry($key, $arguments);
	}
}
