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

namespace Failnet\addon;
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
class Loader
{
	/**
	 * Constructor.
	 * @param array $addons - The addons we want to load.
	 */
	public function __construct(array $addons = array())
	{
		/* @var $ui Failnet\Core\UI */
		$ui = Bot::getObject('core.ui');

		if(!empty($addons))
		{
			foreach($addons as $addon)
			{
				if($this->loadAddon($addon))
				{
					$ui->system(sprintf('Loaded addon "%1$s" successfully', $addon));
				}
				else
				{
					$ui->warning(sprintf('Failed to load addon "%1$s"', $addon));
					$ui->warning(sprintf('Failure message:  %1$s', $e->getMessage()));
				}
			}
		}
	}

	/**
	 * Loads an addon's metadata object, verifies dependencies, and initializes the addon
	 * @return void
	 *
	 * @throws Failnet\Addon\LoaderException
	 */
	public function loadAddon($addon)
	{
		// @todo phar addon support
		try
		{
			$metadata_path = FAILNET . 'addons/' . $addon . '/Addon/Metadata/' . $addon . '.php';
			if(!file_exists($metadata_path))
				throw new LoaderException(); // @todo exception

			require $metadata_path;
			$metadata_class = "Failnet\\Addon\\Metadata\\$addon";
			if(!class_exists($metadata_class))
				throw new LoaderException(); // @todo exception

			// Here we instantiate the addon's metadata object, and make sure it's the right type of object.
			/* @var $metadata Failnet\Addon\Metadata\MetadataBase */
			$metadata = new $metadata_class;
			if(!($metadata instanceof Metadata\MetadataBase))
				throw new LoaderException(); // @todo exception

			if(!($metadata instanceof Metadata\MetadataInterface))
				throw new LoaderException(); // @todo exception

			// Check dependencies and such here.

			if(!$metadata->meetsTargetVersion())
				throw new LoaderException(); // @todo exception

			if(!$metadata->checkDependencies())
				throw new LoaderException();

			// If the addon's metadata object passes all checks, then we add the addon's directory to the autoloader include path, and initialize the addon itself.
			Bot::getObject('core.autoload')->setPath(FAILNET . "addons/$addon/");
			$metadata->initialize();

			return true;
		}
		catch(LoaderException $e)
		{
			return false;
		}
	}
}
