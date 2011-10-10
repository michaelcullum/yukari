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

namespace Codebite\Yukari\Addon\IRC\Environment;
use \Codebite\Yukari\Kernel;
use \OpenFlame\Framework\Event\Instance as Event;


/**
 * Yukari - Terminal display class,
 * 	    Used to handle displaying Yukari's output to a terminal/command prompt.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Display extends \Codebite\Yukari\Environment\Display
{
	/**
	 * Register our listeners in the event dispatcher.
	 * @return \Codebite\Yukari\Addon\IRC\Environment\Display - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		// Register more UI listeners
		Kernel::registerListener('ui.message.irc', 0, array($this, 'displayIRC'));

		// Display IRC going-ons
		Kernel::registerListener('irc.input.action', -10, function(Event $event) {
			Kernel::trigger(Event::newEvent('ui.message.irc')
				->set('message', sprintf('<- [%2$s] *** %1$s %3$s', $event->get('hostmask')->getNick(), $event->get('target'), $event->get('text'))));
		});
		Kernel::registerListener('irc.input.privmsg', -10, function(Event $event) {
			Kernel::trigger(Event::newEvent('ui.message.irc')
				->set('message', sprintf('<- [%2$s] <%1$s> %3$s', $event->get('hostmask')->getNick(), $event->get('target'), $event->get('text'))));
		});
		Kernel::registerListener('irc.input.notice', -10, function(Event $event) {
			Kernel::trigger(Event::newEvent('ui.message.irc')
				->set('message', sprintf('<- [%2$s] <%1$s NOTICE>  %3$s', $event->get('hostmask')->getNick(), $event->get('target'), $event->get('text'))));
		});

		// Display channel happenings.
		Kernel::registerListener('irc.input.join', -10, function(Event $event) {
			Kernel::trigger(Event::newEvent('ui.message.irc')
				->set('message', sprintf('<- %1$s (%2$s@%3$s) has joined %4$s', $event->get('hostmask')->getNick(), $event->get('hostmask')->getUsername(), $event->get('hostmask')->getHost(), $event->get('channel'))));
		});
		Kernel::registerListener('irc.input.part', -10, function(Event $event) {
			if($event->get('reason') !== NULL)
			{
				Kernel::trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- %1$s (%2$s@%3$s) has left %4$s [Reason: %5$s]', $event->get('hostmask')->getNick(), $event->get('hostmask')->getUsername(), $event->get('hostmask')->getHost(), $event->get('channel'), $event->get('reason'))));
			}
			else
			{
				Kernel::trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- %1$s (%2$s@%3$s) has left %4$s', $event->get('hostmask')->getNick(), $event->get('hostmask')->getUsername(), $event->get('hostmask')->getHost(), $event->get('channel'))));
			}
		});
		Kernel::registerListener('irc.input.kick', -10, function(Event $event) {
			if($event->get('reason') !== NULL)
			{
				Kernel::trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- %1$s kicked %2$s %3$s [Reason: %4$s]', $event->get('hostmask')->getNick(), $event->get('user'), $event->get('channel'), $event->get('reason'))));
			}
			else
			{
				Kernel::trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- %1$s kicked %2$s from %3$s', $event->get('hostmask')->getNick(), $event->get('user'), $event->get('channel'))));
			}
		});
		Kernel::registerListener('irc.input.quit', -10, function(Event $event) {
			if(!$event->exists('args') || $event->get('args') === NULL)
			{
				Kernel::trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- %1$s (%2$s@%3$s) has quit [Reason: %4$s]', $event->get('hostmask')->getNick(), $event->get('hostmask')->getUsername(), $event->get('hostmask')->getHost(), $event->get('reason'))));
			}
			else
			{
				Kernel::trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- %1$s (%2$s@%3$s) has quit', $event->get('hostmask')->getNick(), $event->get('hostmask')->getUsername(), $event->get('hostmask')->getHost())));
			}
		});

		// Display CTCP requests and replies
		Kernel::registerListener('irc.input.ctcp', -10, function(Event $event) {
			if(!$event->exists('args') || $event->get('args') === NULL)
			{
				Kernel::trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- <%1$s> CTCP %2$s - %3$s', $event->get('hostmask')->getNick(), $event->get('command'), $event->get('args'))));
			}
			else
			{
				Kernel::trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- <%1$s> CTCP %2$s', $event->get('hostmask')->getNick(), $event->get('command'))));
			}
		});
		Kernel::registerListener('irc.input.ctcp_reply', -10, function(Event $event) {
			if(!$event->exists('args') || $event->get('args') === NULL)
			{
				Kernel::trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- <%1$s> CTCP-REPLY %2$s - %3$s', $event->get('hostmask')->getNick(), $event->get('command'), $event->get('args'))));
			}
			else
			{
				Kernel::trigger(Event::newEvent('ui.message.irc')
					->set('message', sprintf('<- <%1$s> CTCP-REPLY %2$s', $event->get('hostmask')->getNick(), $event->get('command'))));
			}
		});

		// Display our responses
		Kernel::registerListener('irc.postdispatch', -10, function(Event $event) {
			$response = $event->get('event');
			switch($response->getName())
			{
				case 'irc.output.action':
					Kernel::trigger(Event::newEvent('ui.message.irc')
						->set('message', sprintf('-> [%1$s] *** %2$s', $response->get('target'), $response->get('text'))));
				break;

				case 'irc.output.ctcp':
					if($response->exists('args') && $response->get('args') !== NULL)
					{
						Kernel::trigger(Event::newEvent('ui.message.irc')
							->set('message', sprintf('-> [%1$s] CTCP %2$s - %3$s', $response->get('target'), $response->get('command'), $response->get('args'))));
					}
					else
					{
						Kernel::trigger(Event::newEvent('ui.message.irc')
							->set('message', sprintf('-> [%1$s] CTCP %2$s', $response->get('target'), $response->get('command'))));
					}
				break;

				case 'irc.output.ctcp_reply':
					if($response->exists('args') && $response->get('args') !== NULL)
					{
						Kernel::trigger(Event::newEvent('ui.message.irc')
							->set('message', sprintf('-> [%1$s] CTCP-REPLY %2$s - %3$s', $response->get('target'), $response->get('command'), $response->get('args'))));
					}
					else
					{
						Kernel::trigger(Event::newEvent('ui.message.irc')
							->set('message', sprintf('-> [%1$s] CTCP-REPLY %2$s', $response->get('target'), $response->get('command'))));
					}
				break;

				case 'irc.output.privmsg':
					Kernel::trigger(Event::newEvent('ui.message.irc')
						->set('message', sprintf('-> [%1$s] %2$s', $response->get('target'), $response->get('text'))));
				break;

				case 'irc.output.notice':
					Kernel::trigger(Event::newEvent('ui.message.irc')
						->set('message', sprintf('-> [%1$s NOTICE] %2$s', $response->get('target'), $response->get('text'))));
				break;

				default:
					return NULL;
				break;
			}
		});

		return $this;
	}

	/**
	 * Method called on message being received/sent
	 * @param \OpenFlame\Framework\Event\Instance $event - The event that is triggering the output.
	 * @return void
	 */
	public function displayIRC(Event $event)
	{
		$this->output(self::OUTPUT_NORMAL, '', '[irc] %s', $event->get('message'));
	}
}
