<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     addon
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

namespace Failnet\Addon;
use Failnet\Bot as Bot;
use Failnet\Lib as Lib;

/**
 * Failnet - Addon manager class,
 * 	    Manages the loading and initialization of Failnet addons.
 *
 *
 * @category    Failnet
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
class Loader implements Iterator
{
	/**
	 * @var array - Array of instantiated metadata objects.
	 */
	protected $metadata = array();

	/**
	 * Loads an addon's metadata object, verifies dependencies, and initializes the addon
	 * @return void
	 *
	 * @throws Failnet\Addon\LoaderException
	 */
	public function loadAddon($addon)
	{
		// Check to see if the addon has already been loaded.
		if(isset($this->metadata[hash('md5', $addon)]))
			return;

		$using_phar = false;
		// Check to see if there's a phar we are dealing with here before moving on to try to load the standard class files.
		$phar_path = FAILNET . 'addons/' . $addon . '/' . $addon . '.phar';
		if(file_exists($phar_path))
		{
			require $phar_path;
			$using_phar = true;
		}
		else
		{
			$metadata_path = FAILNET . 'addons/' . $addon . '/Addon/Metadata/' . $addon . '.php';
			if(!file_exists($metadata_path))
				throw new LoaderException('Could not locate the addon metadata file', LoaderException::ERR_METADATA_FILE_MISSING);

			require $metadata_path;
		}

		$metadata_class = "Failnet\\Addon\\Metadata\\$addon";
		if(!class_exists($metadata_class))
			throw new LoaderException('Addon metadata class could not be located', LoaderException::ERR_METADATA_CLASS_MISSING);

		// Here we instantiate the addon's metadata object, and make sure it's the right type of object.
		/* @var $metadata Failnet\Addon\Metadata\MetadataBase */
		$metadata = new $metadata_class;
		if(!($metadata instanceof Metadata\MetadataBase))
			throw new LoaderException('Addon metadata class does not extend class MetadataBase', LoaderException::ERR_METADATA_NOT_BASE_CHILD);

		if(!($metadata instanceof Metadata\MetadataInterface))
			throw new LoaderException('Addon metadata class does not implement interface MetadataInterface', LoaderException::ERR_METADATA_NOT_INTERFACE_CHILD);

		// Check dependencies and requirements here.
		if(!$metadata->meetsTargetVersion())
			throw new LoaderException('Installed version of Failnet does not meet the required version for the addon', LoaderException::ERR_METADATA_MINIMUM_TARGET_NOT_MET);

		if(!$metadata->checkDependencies())
			throw new LoaderException('Addon metadata object declares that its required dependencies have not been met', LoaderException::ERR_METADATA_CUSTOM_DEPENDENCY_FAIL);

		// If we aren't using a phar here we need to add the addon's path to the autoload paths.
		if(!$using_phar)
		{
			// If the addon's metadata object passes all checks and we're not using a phar file, then we add the addon's directory to the autoloader include path
			Bot::getObject('core.autoload')->setPath(FAILNET . "addons/$addon/");
		}

		// Initialize the addon
		$metadata->initialize();

		// Store the metadata object in a predictable slot.
		$this->metadata[hash('md5', $addon)] = $metadata;
	}

	/**
	 * Iterator methods
	 */

	/**
	 * Iterator method, rewinds the array back to the first element.
	 * @return void
	 */
	public function rewind()
	{
		return reset($this->metadata);
	}

	/**
	 * Iterator method, returns the key of the current element
	 * @return scalar - The key of the current element.
	 */
	public function key()
	{
		return key($this->metadata);
	}

	/**
	 * Iterator method, checks to see if the current position is valid.
	 * @return boolean - Whether or not the current array position is valid.
	 */
	public function valid()
	{
		return (!is_null(key($this->metadata)));
	}

	/**
	 * Iterator method, gets the current element
	 * @return Failnet\Addon\Metadata\MetadataBase - The current addon metadata object of focus.
	 */
	public function current()
	{
		return current($this->metadata);
	}

	/**
	 * Iterator method, moves to the next session available.
	 * @return void
	 */
	public function next()
	{
		next($this->metadata);
	}
}
