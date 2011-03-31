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

namespace Yukari\Addon\Commander;
use Yukari\Kernel;

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
	 * @return \Yukari\Addon\Commander\Interpreter - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		$dispatcher = Kernel::getDispatcher();
		$dispatcher->register('irc.input.privmsg', array(Kernel::get('addon.commander'), 'handlePrivmsg'));

		return $this;
	}

	/**
	 * Handle and interpret PRIVMSG events, and fire off additional events if we deem the input to be commands directed at the bot.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event to interpret.
	 * @return array - Returns an array of IRC output events to send.
	 */
	public function handlePrivmsg(\OpenFlame\Framework\Event\Instance $event)
	{
		$dispatcher = Kernel::getDispatcher();
		$indicator = Kernel::getConfig('commander.command_indicator');
		$our_name = Kernel::getConfig('irc.nickname');

		$results = array();

		// Is this a direct, private command?
		if($event->getDataPoint('target') == $our_name)
		{
			// Just drop the indicator if this is a private command.  User friendliness and all that. ;)
			if(substr($event->getDataPoint('text'), 0, strlen($indicator)) == $indicator)
			{
				$text = array_pad(explode(' ', substr($event->getDataPoint('text'), strlen($indicator)), 2), 2, '');
			}
			else
			{
				$text = array_pad(explode(' ', $event->getDataPoint('text'), 2), 2, '');
			}

			$_results = $dispatcher->trigger(\OpenFlame\Framework\Event\Instance::newEvent(sprintf('irc.input.command.%s', $text[0]))->setData(array(
				'rootevent'		=> $event,
				'is_private'	=> true,
				'command'		=> $text[0],
				'text'			=> $text[1],
				'target'		=> $event->getDataPoint('target'),
				'hostmask'		=> $event->getDataPoint('hostmask'),
			)));
			$results = array_merge($results, (array) $_results->getReturns());

			$_results = $dispatcher->trigger(\OpenFlame\Framework\Event\Instance::newEvent(sprintf('irc.input.privatecommand.%s', $text[0]))->setData(array(
				'rootevent'		=> $event,
				'is_private'	=> true,
				'command'		=> $text[0],
				'text'			=> $text[1],
				'target'		=> $event->getDataPoint('target'),
				'hostmask'		=> $event->getDataPoint('hostmask'),
			)));
			$results = array_merge($results, (array) $_results->getReturns());
		}
		elseif(preg_match('#^(' . preg_quote($indicator, '#') . '|' . preg_quote($our_name, '#') . '\: )([a-z0-9]*)( (.*))?#iS', $event->getDataPoint('text'), $matches) == true)
		{
			// Make sure we have a full array here.
			list(, $trigger, $command, , $text) = array_pad($matches, 5, '');

			$_results = $dispatcher->trigger(\OpenFlame\Framework\Event\Instance::newEvent(sprintf('irc.input.command.%s', $command))->setData(array(
				'rootevent'		=> $event,
				'is_private'	=> false,
				'command'		=> $command,
				'text'			=> $text,
				'target'		=> $event->getDataPoint('target'),
				'hostmask'		=> $event->getDataPoint('hostmask'),
			)));
			$results = array_merge($results, (array) $_results->getReturns());

			// Check to see if this was the command indicator, or if we were addressed by name ("!command" versus "Yukari: command")
			if($trigger == $indicator)
			{
				// Okay, this was a named command - we treat this as special, and dispatch another event for it.
				$_results = $dispatcher->trigger(\OpenFlame\Framework\Event\Instance::newEvent(sprintf('irc.input.namedcommand.%s', $command))->setData(array(
					'rootevent'		=> $event,
					'is_private'	=> false,
					'command'		=> $command,
					'text'			=> $text,
					'target'		=> $event->getDataPoint('target'),
					'hostmask'		=> $event->getDataPoint('hostmask'),
				)));
				$results = array_merge($results, (array) $_results->getReturns());
			}
		}

		return $results;
	}

	/**
	 * Handle and interpret IRC response events, and fire off additional events for the individual response types.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event to interpret.
	 * @return array - Returns an array of IRC output events to send.
	 */
	public function handleResponse(\OpenFlame\Framework\Event\Instance $event)
	{
		$dispatcher = Kernel::getDispatcher();
		$response_map = Kernel::get('core.response_map');

		$event_code = (int) $event->getDataPoint('code');
		$event_type = $response_map->getResponseType($event_code);

		// Just in case we wtf at a non-standard response code.
		if($event_type === false)
		{
			return NULL;
		}

		$results = $dispatcher->trigger(\OpenFlame\Framework\Event\Instance::newEvent(sprintf('irc.input.response.%s', $event_type))->setData(array(
			'rootevent'		=> $event,
			'code'			=> $event_code,
			'description'	=> $event->getDataPoint('description'),
		)));

		return (array) $results->getReturns();
	}
}
