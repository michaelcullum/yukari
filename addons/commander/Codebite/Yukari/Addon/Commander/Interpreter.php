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

namespace Codebite\Yukari\Addon\Commander;
use Codebite\Yukari\Kernel;
use \OpenFlame\Framework\Event\Instance as Event;

/**
 * Yukari - Commander addon interpreter object,
 *      Checks incoming privmsg events and sees if they are commands intended for the bot, and if so extended events are issued accordingly.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Interpreter
{
	/**
	 * Register the listeners we need for this addon to work properly.
	 * @return \Codebite\Yukari\Addon\Commander\Interpreter - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		Kernel::registerListener('irc.input.privmsg', -3, array($this, 'handlePrivmsg'));
		Kernel::registerListener('irc.input.response', -3, array($this, 'handleResponse'));

		return $this;
	}

	/**
	 * Handle and interpret PRIVMSG events, and fire off additional events if we deem the input to be commands directed at the bot.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event to interpret.
	 * @return array - Returns an array of IRC output events to send.
	 */
	public function handlePrivmsg(Event $event)
	{
		$indicator = Kernel::getConfig('commander.command_indicator');
		$our_name = Kernel::get('irc.stack')->getNetworkOption($event->get('network'), 'nickname');

		$results = array();

		// Is this a direct, private command?
		if(mb_strtolower($event->get('target')) == mb_strtolower($our_name))
		{
			// Just drop the indicator if this is a private command.  User friendliness and all that. ;)
			if(substr($event->get('text'), 0, strlen($indicator)) == $indicator)
			{
				$text = array_pad(explode(' ', substr($event->get('text'), strlen($indicator)), 2), 2, '');
			}
			else
			{
				$text = array_pad(explode(' ', $event->get('text'), 2), 2, '');
			}

			$_results = Kernel::trigger(Event::newEvent(sprintf('irc.input.command.%s', $text[0]))->setData(array(
				'rootevent'		=> $event,
				'is_private'	=> true,
				'command'		=> $text[0],
				'text'			=> $text[1],
				'target'		=> $event->get('target'),
				'hostmask'		=> $event->get('hostmask'),
				'network'		=> $event->get('network'),
				'mname'			=> $event->get('mname'),
			)));
			$results = array_merge($results, $this->compactArray($_results->getReturns()));

			$_results = Kernel::trigger(Event::newEvent(sprintf('irc.input.privatecommand.%s', $text[0]))->setData(array(
				'rootevent'		=> $event,
				'is_private'	=> true,
				'command'		=> $text[0],
				'text'			=> $text[1],
				'target'		=> $event->get('target'),
				'hostmask'		=> $event->get('hostmask'),
				'network'		=> $event->get('network'),
				'mname'			=> $event->get('mname'),
			)));
			$results = array_merge($results, $this->compactArray($_results->getReturns()));
		}
		elseif(preg_match('#^(' . preg_quote($indicator, '#') . '|' . preg_quote($our_name, '#') . '\: )([a-z0-9]*)( (.*))?#iS', $event->get('text'), $matches) == true)
		{
			// Make sure we have a full array here.
			list(, $trigger, $command, , $text) = array_pad($matches, 5, '');

			$_results = Kernel::trigger(Event::newEvent(sprintf('irc.input.command.%s', $command))->setData(array(
				'rootevent'		=> $event,
				'is_private'	=> false,
				'command'		=> $command,
				'text'			=> $text,
				'target'		=> $event->get('target'),
				'hostmask'		=> $event->get('hostmask'),
				'network'		=> $event->get('network'),
				'mname'			=> $event->get('mname'),
			)));
			$results = array_merge($results, $this->compactArray($_results->getReturns()));

			// Check to see if this was the command indicator, or if we were addressed by name ("!command" versus "Yukari: command")
			if($trigger == $indicator)
			{
				// Okay, this was a named command - we treat this as special, and dispatch another event for it.
				$_results = Kernel::trigger(Event::newEvent(sprintf('irc.input.namedcommand.%s', $command))->setData(array(
					'rootevent'		=> $event,
					'is_private'	=> false,
					'command'		=> $command,
					'text'			=> $text,
					'target'		=> $event->get('target'),
					'hostmask'		=> $event->get('hostmask'),
					'network'		=> $event->get('network'),
					'mname'			=> $event->get('mname'),
				)));
				$results = array_merge($results, $this->compactArray($_results->getReturns()));
			}
		}

		return $results;
	}

	public function compactArray($array, $recurse = true)
	{
		$return = array();

		if($array !== NULL)
		{
			foreach($array as $sub)
			{
				if(is_array($sub))
				{
					$return = array_merge($return, ($recurse && is_array($sub)) ? $this->compactArray($sub, false) : $sub);
				}
				else
				{
					$return = array_merge($return, array($sub));
				}
			}
		}

		return $return;
	}

	/**
	 * Handle and interpret IRC response events, and fire off additional events for the individual response types.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event to interpret.
	 * @return array - Returns an array of IRC output events to send.
	 */
	public function handleResponse(Event $event)
	{
		$response_map = Kernel::get('irc.response_map');

		$event_code = (int) $event->get('code');
		$event_type = $response_map->getResponseType($event_code);

		// Just in case we wtf at a non-standard response code.
		if($event_type === false)
		{
			return NULL;
		}

		$results = Kernel::trigger(Event::newEvent(sprintf('irc.input.response.%s', $event_type))->setData(array(
			'event'			=> $event,
			'code'			=> $event_code,
			'description'	=> $event->get('description'),
		)));

		$return = $this->compactArray($results->getReturns());

		return $return;
	}
}
