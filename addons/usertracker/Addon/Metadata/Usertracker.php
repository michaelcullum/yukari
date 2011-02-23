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
 * Yukari - Addon metadata object,
 *      Provides some information regarding the addon.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Usertracker extends \Yukari\Addon\Metadata\MetadataBase
{
	/**
	 * @var string - The addon's version.
	 */
	protected $version = 'core';

	/**
	 * @var string - The addon's author information.
	 */
	protected $author = 'Damian Bushong';

	/**
	 * @var string - The addon's name.
	 */
	protected $name = 'UserTracker';

	/**
	 * @var string - The addon's description.
	 */
	protected $description = 'Populates and maintains a local cache of user hostmasks, and each user\'s channel modes in the channels that the bot is inhabiting.';

	/**
	 * Hooking method for addon metadata objects, called to initialize the addon after the dependency check has been passed.
	 * @return void
	 */
	public function initialize()
	{
		$usertracker = Kernel::set('addon.usertracker', new \Yukari\Addon\DataTracking\UserTracker());
		$usertracker->registerListeners();
	}

	/**
	 * Hooking method for addon metadata objects for executing own code on pre-load dependency check.
	 * @return boolean - Does the addon pass the dependency check?
	 *
	 * @throws \RuntimeException
	 */
	public function checkDependencies()
	{
		$addon_loader = Kernel::get('core.addonloader');

		if(!Kernel::get('addon.commander'))
		{
			try
			{
				$addon_loader->loadAddon('commander');
			}
			catch(\RuntimeException $e)
			{
				throw new \RuntimeException(sprintf('Failed to load dependency "addon.commander", error message "%1$s"', $e->getMessage());
			}
		}

		if(!Kernel::get('addon.channeltracker'))
		{
			try
			{
				$addon_loader->loadAddon('channeltracker');
			}
			catch(\RuntimeException $e)
			{
				throw new \RuntimeException(sprintf('Failed to load dependency "addon.channeltracker", error message "%1$s"', $e->getMessage());
			}
		}

		return true;
	}
}
