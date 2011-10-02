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
 * @copyright   (c) 2009 - 2011 Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Codebite\Yukari\Addon\ACL;
use Codebite\Yukari\Kernel;
use \OpenFlame\Framework\Event\Instance as Event;
use \OpenFlame\Framework\Utility\JSON;

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
			Kernel::setConfig('acl.whitelist.file', 'acl_whitelist.json');
		}
	}

	/**
	 * Load the file that contains our whitelist data
	 * @return \Codebite\Yukari\Addon\ACL\Whitelist - Provides a fluent interface.
	 */
	public function loadWhitelistFile()
	{
		$this->whitelist = JSON::decode(YUKARI . '/data/config/addons/' . Kernel::getConfig('acl.whitelist.file'));
		$this->whitelist_regexp = \Codebite\Yukari\hostmasksToRegex((array) $this->whitelist['whitelist_data']);

		return $this;
	}

	/**
	 * Register the listeners we need for this addon to work properly.
	 * @return \Codebite\Yukari\Addon\ACL\Whitelist - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		Kernel::registerListener('acl.check_allowed', 0, array(Kernel::get('addon.acl'), 'handleAccess'));
		Kernel::registerListener('irc.input.command.reloadwhitelist', 0, array(Kernel::get('addon.acl'), 'handleReloadWhitelist'));

		return $this;
	}

	/**
	 * Handle and interpret command permission events.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event to interpret.
	 * @return integer - Returns 1 if user is authorized, returns 0 if not authorized.
	 */
	public function handleAccess(Event $event)
	{
		// Break the trigger cycle
		$event->breakTrigger();
		$result = preg_match($this->whitelist_regexp, $event->get('hostmask'));
		return (int) $result;
	}

	/**
	 * Handle the command to reload the whitelist.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event to interpret.
	 * @return array - Array of events to dispatch in response to the input event.
	 */
	public function handleReloadWhitelist(Event $event)
	{
		$highlight = (!$event->get('is_private')) ? $event->get('hostmask')->getNick() . ':' : '';
		if($this->handleAccess($event) === 1)
		{
			$this->loadWhitelistFile();

			$results = Event::newEvent('irc.output.privmsg')
				->set('target', $event->get('target'))
				->set('text', sprintf('%1$s Whitelist file reloaded.', $highlight));
		}
		else
		{
			$results = Event::newEvent('irc.output.privmsg')
				->set('target', $event->get('target'))
				->set('text', sprintf('%1$s You are not authorized to use this command.', $highlight));
		}

		return $results;
	}
}
