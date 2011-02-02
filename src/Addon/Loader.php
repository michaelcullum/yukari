<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     addon
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

namespace Yukari\Addon;
use Yukari\Kernel;

/**
 * Yukari - Addon manager class,
 * 	    Manages the loading and initialization of addons.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
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
	 * @throws \RuntimeException
	 * @throws \LogicException
	 */
	public function loadAddon($addon)
	{
		// Check to see if the addon has already been loaded.
		if(isset($this->metadata[hash('md5', $addon)]))
			return;

		$using_phar = false;
		// Check to see if there's a phar we are dealing with here before moving on to try to load the standard class files.
		$phar_path = YUKARI . "/addons/{$addon}/{$addon}.phar";
		$metadata_path = "/Addon/Metadata/{$addon}.php";
		if(file_exists($phar_path))
		{
			require $phar_path;
			$using_phar = true;

			require $phar_path . $metadata_path;
		}
		else
		{
			$metadata_path = YUKARI . "/addons/{$addon}/Addon/Metadata/{$addon}.php";
			if(!file_exists(YUKARI . "/addons{$metadata_path}"))
				throw new \RuntimeException('Could not locate addon metadata file');

			require YUKARI . "/addons{$metadata_path}";
		}

		$metadata_class = "\\Yukari\\Addon\\Metadata\\{$addon}";
		if(!class_exists($metadata_class))
			throw new \RuntimeException('Addon metadata class not defined');

		// We want to instantiate the addon's metadata object, and make sure it's the right type of object.
		/* @var $metadata \Yukari\Addon\Metadata\MetadataBase */
		$metadata = new $metadata_class;
		if(!($metadata instanceof \Yukari\Addon\Metadata\MetadataBase))
			throw new \LogicException('Addon metadata class does not extend class MetadataBase');

		if(!($metadata instanceof \Yukari\Addon\Metadata\MetadataInterface))
			throw new \LogicException('Addon metadata class does not implement interface MetadataInterface');

		// Check dependencies and requirements here.
		if(!$metadata->meetsTargetVersion())
			throw new \RuntimeException('Installed version of Yukari does not meet the required version for the addon');

		if(!$metadata->checkDependencies())
			throw new \RuntimeException('Addon metadata object declares that its required dependencies have not been met');

		// If we aren't using a phar here we need to add the addon's path to the autoload paths.
		if(!$using_phar)
		{
			// If the addon's metadata object passes all checks and we're not using a phar file, then we add the addon's directory to the autoloader include path
			Kernel::getAutoloader()->setPath(YUKARI . "/addons/{$addon}/");
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
	 * @return \Yukari\Addon\Metadata\MetadataBase - The current addon metadata object of focus.
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
