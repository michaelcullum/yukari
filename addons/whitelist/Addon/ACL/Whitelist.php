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

namespace Yukari\Addon\ACL;
use Yukari\Kernel;

/**
 * Yukari - ACL Whitelist object,
 *      Provides simple command access whitelisting functionality.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Whitelist
{
	/**
	 * @var array - The array of whitelist data obtained from the config
	 */
	protected $whitelist = array();

	/**
	 * @var string - The compiled regexp used for the whitelist
	 */
	protected $whitelist_regexp = '';

	/**
	 * Constructor
	 * @return void
	 */
	public function __construct()
	{
		// Set a default value for this config if it's not present.
		if(!Kernel::getConfig('acl.whitelist.file'))
			Kernel::setConfig('acl.whitelist.file', 'acl_whitelist.yml');
	}

	/**
	 * Load the file that contains our whitelist data
	 * @return \Yukari\Addon\ACL\Whitelist - Provides a fluent interface.
	 */
	public function loadWhitelistFile()
	{
		$this->whitelist = \sfYaml::load(YUKARI . '/data/config/addons/' . Kernel::getConfig('acl.whitelist.file'));
		$this->whitelist_regexp = \Yukari\hostmasksToRegex($this->whitelist);

		return $this;
	}

	/**
	 * Register the listeners we need for this addon to work properly.
	 * @return \Yukari\Addon\ACL\Whitelist - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		$dispatcher = Kernel::getDispatcher();
		$dispatcher->register('acl.check_allowed', array(Kernel::get('addon.acl'), 'handleAccess'));

		return $this;
	}

	/**
	 * Handle and interpret command permission events.
	 * @param \Yukari\Event\Instance $event - The event to interpret.
	 * @return unknown
	 */
	public function handleAccess(\Yukari\Event\Instance $event)
	{
		$result = preg_match($this->whitelist_regexp, $event->getDataPoint('hostmask'));
		return (int) $result;
	}
}
