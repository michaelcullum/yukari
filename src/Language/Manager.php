<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
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
use Yukari\Kernel;

/**
 * Yukari - Language manager class,
 * 	    Collects and provides access to all of the language entries for Yukari.
 *
 *
 * @category    Yukari
 * @package     language
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
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
	 * Set the path to search for language files in
	 * @param string $language_dir - The directory to load language files from.
	 * @return \Yukari\Language\Manager - Provides a fluent interface
	 */
	public function setPath($language_dir)
	{
		$this->language_dir = rtrim($language_dir, '/') . '/';

		return $this;
	}

	/**
	 * Loads all present language files
	 * @return void
	 */
	public function collectEntries()
	{
		$dispatcher = Kernel::getDispatcher();

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
			catch(\RuntimeException $e)
			{
				$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'ui.message.debug')
					->setDataPoint('message', sprintf('Failed to load language file "%s"', $file)));
			}
		}
	}

	/**
	 * Load a language file
	 * @param string $file - The full filepath of the language file to load.
	 * @return void
	 *
	 * @throws \RuntimeException
	 *
	 * @note This method will not allow reloading a language file
	 */
	public function loadFile($file)
	{
		$dispatcher = Kernel::getDispatcher();

		$file = basename($file);
		if(in_array($file, $this->files))
			throw new \RuntimeException(sprintf('Language file "%1$s" cannot be reloaded', $file));

		// Let's try to load the language file...we use try/catch in case something goes nuclear with the JSON processing, here.
		try
		{
			$json = \Yukari\Lib\JSON::decode($this->language_dir . $file);
		}
		catch(\RuntimeException $e)
		{
			throw new \RuntimeException(sprintf('Language file "%1$s" could not be loaded', $file));
		}

		// Store the new language entries
		$this->setEntries($json['locale'], $json['entries']);

		// Add this language file to the list of loaded language files
		$this->files[] = $file;
		$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'ui.message.system')
			->setDataPoint('message', sprintf('Loaded language file "%s"', $file)));
	}

	/**
	 * Pulls a language variable from Yukari, and will vsprintf() extra strings into the language variable if desired
	 * @param string $locale - The locale to grab the entry under.
	 * @param string $key - The language key of the variable to fetch
	 * @param array $arguments - Any parameters that should be passed to vsprintf() if desired
	 * @return string - The desired language variable, parsed by vsprintf() if desired
	 */
	public function getEntry($locale, $key, array $arguments = array())
	{
		if(!isset($this->entries[$locale]))
			$locale = Kernel::getConfig('language.default_locale');

		// If there's no such language key, we'll return an empty string
		if(!isset($this->entries[$locale][$key]))
			return $key;

		if(!empty($arguments))
			return $this->entries[$locale][$key];

		// Okay, someone's gotta be difficult.  We need to vsprintf(), yay.
		return vsprintf($this->entries[$locale][$key], $arguments);
	}

	/**
	 * Load a language variable into Yukari for future use.
	 * @param string $locale - The locale for this language entry.
	 * @param string $key - The language key to use this language variable with.
	 * @param string $value - A plain or sprintf() formatted string to use for a language variable.
	 * @return void
	 *
	 * @note This method WILL overwrite previously loaded language variables!
	 */
	public function setEntry($locale, $key, $value)
	{
		$this->entries[$locale][strtoupper($key)] = $value;
	}

	/**
	 * Load up an array of language variables.
	 * @param string $locale - The locale to store the variables for.
	 * @param array $entries - The array of entries to load.
	 * @return void
	 *
	 * @note This method WILL overwrite previously loaded language variables!
	 */
	public function setEntries($locale, array $entries)
	{
		foreach($entries as $key => $value)
		{
			$this->setEntry($locale, $key, $value);
		}
	}

	/**
	 * Alias of \Yukari\Language\Manager->getEntry()
	 * @see \Yukari\Language\Manager->getEntry()
	 */
	public function __invoke($locale, $key, array $arguments = array())
	{
		return $this->getEntry($locale, $key, $arguments);
	}
}
