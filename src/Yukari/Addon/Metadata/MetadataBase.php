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

namespace Yukari\Addon\Metadata;
use Yukari\Kernel;

/**
 * Yukari - Addon metadata base class,
 *      Defines common methods and properties for addon metadata objects to use.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
abstract class MetadataBase
{
	/**
	 * @var string - The addon's version.
	 */
	protected $version = '';

	/**
	 * @var string - The addon's author information.
	 */
	protected $author = '';

	/**
	 * @var string - The addon's name.
	 */
	protected $name = '';

	/**
	 * @var string - The addon's description.
	 */
	protected $description = '';

	/**
	 * @ignore - preventing use of __construct on metadata objects
	 */
	final public function __construct() { }

	/**
	 * Loads an addon dependency for the current addon, complete with error handling
	 * @param string $slot - The slot to check for the dependency in.
	 * @param string $name - The name of the addon dependency to load if the slot isn't occupied
	 * @return boolean - Returns true if the dependency is loaded.
	 *
	 * @throws \RuntimeException
	 */
	final public function loadDependency($slot, $name)
	{
		$dispatcher = Kernel::getDispatcher();
		$addon_loader = Kernel::get('core.addonloader');
		if(!Kernel::get($slot))
		{
			try
			{
				$addon_loader->loadAddon($name);
				$dispatcher->trigger(\OpenFlame\Framework\Event\Instance::newEvent('ui.message.system')
					->setDataPoint('message', sprintf('Loaded addon "%s"', $name)));
				return true;
			}
			catch(\RuntimeException $e)
			{
				throw new \RuntimeException(sprintf('Failed to load dependency "%1$s", error message "%2$s"', $name, $e->getMessage()));
			}
		}
		return true;
	}

	/**
	 * Get the version stamp of the addon.
	 * @return string - The version stamp of the addon.
	 */
	final public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Get the author for the addon.
	 * @return string - The author data for the addon.
	 */
	final public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * Get the name of the addon.
	 * @return string - The addon's name.
	 */
	final public function getName()
	{
		return $this->name;
	}

	/**
	 * Get this addon's description
	 * @return string - The addon description.
	 */
	final public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Hooking method for addon metadata objects, called to initialize the addon after the dependency check has been passed.
	 * @return void
	 */
	public function initialize() { }

	/**
	 * Hooking method for addon metadata objects for executing own code on pre-load dependency check.
	 * @return boolean - Does the addon pass the dependency check?
	 */
	public function checkDependencies() { }
}
