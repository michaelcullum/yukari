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
		{
			Kernel::setConfig('acl.whitelist.file', 'acl_whitelist.yml');
		}
	}

	/**
	 * Load the file that contains our whitelist data
	 * @return \Yukari\Addon\ACL\Whitelist - Provides a fluent interface.
	 */
	public function loadWhitelistFile()
	{
		$this->whitelist = \Symfony\Component\Yaml\Yaml::load(YUKARI . '/data/config/addons/' . Kernel::getConfig('acl.whitelist.file'));
		$this->whitelist_regexp = \Yukari\hostmasksToRegex((array) $this->whitelist['whitelist_data']);

		return $this;
	}

	/**
	 * Register the listeners we need for this addon to work properly.
	 * @return \Yukari\Addon\ACL\Whitelist - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		$dispatcher = Kernel::getDispatcher();
		$dispatcher->register('acl.check_allowed', array(Kernel::get('addon.acl'), 'handleAccess'))
			->register('irc.input.command.reloadwhitelist', array(Kernel::get('addon.acl'), 'handleReloadWhitelist'));

		return $this;
	}

	/**
	 * Handle and interpret command permission events.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event to interpret.
	 * @return integer - Returns 1 if user is authorized, returns 0 if not authorized.
	 */
	public function handleAccess(\OpenFlame\Framework\Event\Instance $event)
	{
		// Break the trigger cycle
		$event->breakTrigger();
		$result = preg_match($this->whitelist_regexp, $event->getDataPoint('hostmask'));
		return (int) $result;
	}

	/**
	 * Handle the command to reload the whitelist.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event to interpret.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleReloadWhitelist(\OpenFlame\Framework\Event\Instance $event)
	{
		$highlight = (!$event->getDataPoint('is_private')) ? $event->getDataPoint('hostmask')->getNick() . ':' : '';
		if($this->handleAccess($event) === 1)
		{
			$this->loadWhitelistFile();

			$results = \OpenFlame\Framework\Event\Instance::newEvent('irc.output.privmsg')
				->setDataPoint('target', $event->getDataPoint('target'))
				->setDataPoint('text', sprintf('%1$s Whitelist file reloaded.', $highlight));
		}
		else
		{
			$results = \OpenFlame\Framework\Event\Instance::newEvent('irc.output.privmsg')
				->setDataPoint('target', $event->getDataPoint('target'))
				->setDataPoint('text', sprintf('%1$s You are not authorized to use this command.', $highlight));
		}

		return $results;
	}
}
