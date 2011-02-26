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
class Botadmin extends \Yukari\Addon\Metadata\MetadataBase
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
	protected $name = 'Botadmin';

	/**
	 * @var string - The addon's description.
	 */
	protected $description = 'Provides basic administration of the bot to whitelisted users.';

	/**
	 * Hooking method for addon metadata objects, called to initialize the addon after the dependency check has been passed.
	 * @return void
	 */
	public function initialize()
	{
		$interpreter = Kernel::set('addon.botadmin', new \Yukari\Addon\Admin\Basic());
		$interpreter->registerListeners();
	}

	/**
	 * Hooking method for addon metadata objects for executing own code on pre-load dependency check.
	 * @return boolean - Does the addon pass the dependency check?
	 *
	 * @throws \RuntimeException
	 */
	public function checkDependencies()
	{
		$dispatcher = Kernel::getDispatcher();
		$addon_loader = Kernel::get('core.addonloader');

		if(!Kernel::get('addon.commander'))
		{
			try
			{
				$addon_loader->loadAddon('commander');
				$dispatcher->trigger(\Yukari\Event\Instance::newEvent('ui.message.system')
					->setDataPoint('message', sprintf('Loaded addon "%s"', 'commander')));
			}
			catch(\RuntimeException $e)
			{
				throw new \RuntimeException(sprintf('Failed to load dependency "addon.commander", error message "%1$s"', $e->getMessage()));
			}
		}

		if(!Kernel::get('addon.acl'))
		{
			try
			{
				$addon_loader->loadAddon('whitelist');
				$dispatcher->trigger(\Yukari\Event\Instance::newEvent('ui.message.system')
					->setDataPoint('message', sprintf('Loaded addon "%s"', 'whitelist')));
			}
			catch(\RuntimeException $e)
			{
				throw new \RuntimeException(sprintf('Failed to load dependency "addon.acl", error message "%1$s"', $e->getMessage()));
			}
		}

		return true;
	}
}
