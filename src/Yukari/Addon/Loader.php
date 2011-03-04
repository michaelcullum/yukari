<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
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
class Loader implements \Iterator
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
		{
			return;
		}

		$using_phar = false;
		$addon_uc = ucfirst($addon);
		// Check to see if there's a phar we are dealing with here before moving on to try to load the standard class files.
		$phar_path = "lib/addons/{$addon}.phar";
		$metadata_path = "/Yukari/Addon/Metadata/{$addon_uc}.php";
		if(file_exists(YUKARI . "/{$phar_path}"))
		{
			$using_phar = true;

			if(!file_exists("phar://{$phar_path}/{$metadata_path}"))
			{
				throw new \RuntimeException('Could not locate addon metadata file');
			}

			require "phar://{$phar_path}/{$metadata_path}";
		}
		else
		{
			$dispatcher = Kernel::getDispatcher();
			$dispatcher->trigger(\Yukari\Event\Instance::newEvent($this, 'ui.message.debug')
				->setDataPoint('message', sprintf('Phar archive not present for addon "%1$s", looked in "%2$s"', $addon, YUKARI . "/{$phar_path}")));

			if(!file_exists(YUKARI . "/addons/{$addon}{$metadata_path}"))
			{
				throw new \RuntimeException('Could not locate addon metadata file');
			}

			require YUKARI . "/addons/{$addon}{$metadata_path}";
		}

		$metadata_class = "\\Yukari\\Addon\\Metadata\\{$addon_uc}";
		if(!class_exists($metadata_class))
		{
			throw new \RuntimeException('Addon metadata class not defined');
		}

		// We want to instantiate the addon's metadata object, and make sure it's the right type of object.
		$metadata = new $metadata_class;
		if(!($metadata instanceof \Yukari\Addon\Metadata\MetadataBase))
		{
			throw new \LogicException('Addon metadata class does not extend class MetadataBase');
		}

		// Let our addons check for their dependencies here.
		try
		{
			if(!$metadata->checkDependencies())
			{
				throw new \RuntimeException('Addon metadata object declares that its required dependencies have not been met');
			}
		}
		catch(\Exception $e)
		{
			throw new \RuntimeException(sprintf('Dependency check failed, reason: %1$s', $e->getMessage()));
		}

		// If the addon's metadata object passes all checks and we're not using a phar file, then we add the addon's directory to the autoloader include path
		if($using_phar)
		{
			Kernel::getAutoloader()->setPath("phar://{$phar_path}/");
		}
		else
		{
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
