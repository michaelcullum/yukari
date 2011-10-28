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

namespace Codebite\Yukari\Addon\Autojoin;
use Codebite\Yukari\Kernel;
use \OpenFlame\Framework\Event\Instance as Event;

/**
 * Yukari - Autojoin-er object,
 *      Automatically joins channels on invite or on end of MOTD.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Channel
{
	public function registerListeners()
	{
		Kernel::registerListener('irc.input.invite', 0, array($this, 'handleInvite'));
		Kernel::registerListener('irc.input.response.RPL_ENDOFMOTD', 0, array($this, 'handleEndOfMOTD'));
	}

	public function handleInvite(Event $event)
	{
		$results = array();

		if(Kernel::getConfig('irc.join_invite'))
		{
			if(substr($event->get('text'), 0, 1) !== '#')
			{
				$result = Event::newEvent('irc.output.privmsg')
					->set('target', $event->get('target'))
					->set('text', 'Invalid channel specified.');
			}
			else
			{
				$result = Event::newEvent('irc.output.join')
					->set('channel', $event->get('channel'));
			}
		}
		else
		{
			$result = Event::newEvent('irc.output.privmsg')
				->set('target', $event->get('target'))
				->set('text', 'I\'m not allowed to join channels on invite, sorry.');
		}

		return $results;
	}

	public function handleEndOfMOTD(Event $event)
	{
		$network = $event->get('network');
		$channels = Kernel::get('irc.stack')->getNetworkOption($network, 'autojoin');

		if(!$channels)
		{
			return;
		}

		$joins = array();
		foreach($channels as $channel)
		{
			$joins[] = Event::newEvent('irc.output.join')
				->set('channel', (($channel[0] != '#') ? '#' : '') . $channel);
		}

		return $joins;
	}
}
