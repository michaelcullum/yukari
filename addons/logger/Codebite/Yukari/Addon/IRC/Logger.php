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

namespace Codebite\Yukari\Addon\IRC;
use \Codebite\Yukari\Kernel;
use \OpenFlame\Framework\Event\Instance as Event;


/**
 * Yukari - IRC logging class,
 * 	    Used to log IRC going-ons.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Logger
{
	public function registerListeners()
	{
		Kernel::registerListener('irc.input.action', -5, array($this, 'handleIRCEvent'));
		Kernel::registerListener('irc.input.privmsg', -5, array($this, 'handleIRCEvent'));
		Kernel::registerListener('irc.input.notice', -5, array($this, 'handleIRCEvent'));
		Kernel::registerListener('irc.input.topic', -5, array($this, 'handleIRCEvent'));
		Kernel::registerListener('irc.input.ctcp', -5, array($this, 'handleIRCEvent'));
		Kernel::registerListener('irc.input.ctcp_reply', -5, array($this, 'handleIRCEvent'));
		Kernel::registerListener('irc.input.invite', -5, array($this, 'handleIRCEvent'));
		Kernel::registerListener('irc.input.join', -5, array($this, 'handleIRCEvent'));
		Kernel::registerListener('irc.input.kick', -5, array($this, 'handleIRCEvent'));
		Kernel::registerListener('irc.input.mode', -5, array($this, 'handleIRCEvent'));
		Kernel::registerListener('irc.input.nick', -5, array($this, 'handleIRCEvent'));
		Kernel::registerListener('irc.input.part', -5, array($this, 'handleIRCEvent'));
		Kernel::registerListener('irc.input.quit', -5, array($this, 'handleIRCEvent'));
	}

	public function handleIRCEvent(Event $event)
	{
		$logger = Kernel::get('db.logger');

		$_explode = explode('.', $event->getName());
		$type = array_pop($_explode);
		$source = $event->get('network') . ' ' . (string) $event->get('hostmask');
		$destination = '';
		$data = array();
		switch($type)
		{
			case 'action':
			case 'privmsg':
			case 'notice':
			case 'topic':
				$destination = $event->get('target');
				$data = array(
					'text'		=> $event->get('text'),
				);
			break;

			case 'ctcp':
			case 'ctcp_reply':
				$destination = $event->get('target');
				$data = array(
					'command'	=> $event->get('command'),
					'args'		=> $event->get('args'),
				);
			break;

			case 'invite':
			case 'join':
				$destination = $event->get('target');
				$data = array(
					'channel'	=> $event->get('channel'),
				);
			break;

			case 'kick':
				$destination = $event->get('channel');
				$data = array(
					'user'		=> $event->get('user'),
					'reason'	=> $event->get('reason'),
				);
			break;

			case 'mode':
				$destination = $event->get('target');
				$data = array(
					'flags'		=> $event->get('flags'),
					'args'		=> $event->get('args'),
				);
			break;

			case 'nick':
				$data = array(
					'nick'		=> $event->get('nick'),
				);
			break;

			case 'part':
				$destination = $event->get('channel');
				$data = array(
					'reason'	=> $event->get('reason'),
				);
			break;

			case 'quit':
				$data = array(
					'reason'	=> $event->get('reason'),
				);
			break;
		}

		$logger->newLogEntry($event->getName(), $source, $destination, $data);
	}
}
