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

namespace Codebite\Yukari\Addon\Metadata;
use Codebite\Yukari\Kernel;
use \OpenFlame\Framework\Event\Instance as Event;

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
class Automation extends \Codebite\Yukari\Addon\Metadata\MetadataBase
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
	protected $name = 'Automation';

	/**
	 * @var string - The addon's description.
	 */
	protected $description = 'Provides basic automation for handling of standard IRC events.';

	/**
	 * Hooking method for addon metadata objects, called to initialize the addon after the dependency check has been passed.
	 * @return void
	 */
	public function initialize()
	{
		$dispatcher = Kernel::get('dispatcher');

		// Respond to CTCP VERSION and CTCP PING (if a valid argument for the CTCP was provided)
		$ctcp_lambda = function(Event $event) {
			if(strtolower($event->get('command')) === 'version')
			{
				return Event::newEvent('irc.output.ctcp_reply')
					->setData(array(
						'command' 	=> 'version',
						'target'	=> $event->get('hostmask')->getNick(),
						'args'		=> sprintf('Yukari IRC Bot - %s', Kernel::getBuildNumber()),
					));
			}
			elseif(strtolower($event->get('command')) === 'ping')
			{
				if(!$event->exists('args') || $event->get('args') === NULL)
					return NULL;

				return Event::newEvent('irc.output.ctcp_reply')
					->setData(array(
						'command' 	=> 'ping',
						'target'	=> $event->get('hostmask')->getNick(),
						'args'		=> $event->get('args'),
					));
			}
			else
			{
				return NULL;
			}
		};
		$dispatcher->register('irc.input.ctcp', -10, $ctcp_lambda); // use -10 priority for medium-high listener priority

		// Respond to server pings
		$ping_lambda = function(Event $event) {
			return Event::newEvent('irc.output.pong')
					->set('origin', $event->get('target'));
		};
		$dispatcher->register('irc.input.ping', -10, $ping_lambda); // use -10 priority for medium-high listener priority
	}

	/**
	 * Hooking method for addon metadata objects for executing own code on pre-load dependency check.
	 * @return boolean - Does the addon pass the dependency check?
	 */
	public function checkDependencies()
	{
		// This addon has no dependencies.
		return true;
	}
}
