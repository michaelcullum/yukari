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
	 * @param \Yukari\Event\Instance $event - The event to interpret.
	 * @return array - Returns an array of IRC output events to send.
	 */
	public function handlePrivmsg(\Yukari\Event\Instance $event)
	{
		$dispatcher = Kernel::getDispatcher();
		$indicator = Kernel::getConfig('commander.command_indicator');
		$our_name = Kernel::getConfig('irc.nickname');

		$results = array();

		// Is this a direct, private command?
		if($event['target'] == $our_name)
		{
			// Just drop the indicator if this is a private command.  User friendliness and all that. ;)
			if(substr($event['text'], 0, strlen($indicator)) == $indicator)
			{
				$text = array_pad(explode(' ', substr($event['text'], strlen($indicator)), 2), 2, '');
			}
			else
			{
				$text = array_pad(explode(' ', $event['text'], 2), 2, '');
			}

			$_results = $dispatcher->trigger(\Yukari\Event\Instance::newEvent(sprintf('irc.input.command.%s', $text[0]))
				->setDataPoint('command', $text[0])
				->setDataPoint('text', $text[1])
				->setDataPoint('target', $event['target'])
				->setDataPoint('hostmask', $event['hostmask'])
				->setDataPoint('is_private', true)
				->setDataPoint('rootevent', $event));
			if(!is_array($_results))
				$_results = array($_results);
			$results = array_merge($results, $_results);

			$_results = $dispatcher->trigger(\Yukari\Event\Instance::newEvent(sprintf('irc.input.privatecommand.%s', $text[0]))
				->setDataPoint('command', $text[0])
				->setDataPoint('text', $text[1])
				->setDataPoint('target', $event['target'])
				->setDataPoint('hostmask', $event['hostmask'])
				->setDataPoint('is_private', true)
				->setDataPoint('rootevent', $event));
				$_results = array($_results);
			$results = array_merge($results, $_results);
		}
		elseif(preg_match('#^(' . preg_quote($indicator, '#') . '|' . preg_quote($our_name, '#') . '\: )([a-z0-9]*)( (.*))?#iS', $event['text'], $matches) == true)
		{
			// Make sure we have a full array here.
			list(, $trigger, $command, , $text) = array_pad($matches, 5, '');

			$_results = $dispatcher->trigger(\Yukari\Event\Instance::newEvent(sprintf('irc.input.command.%s', $command))
				->setDataPoint('command', $command)
				->setDataPoint('text', $text)
				->setDataPoint('target', $event['target'])
				->setDataPoint('hostmask', $event['hostmask'])
				->setDataPoint('is_private', false)
				->setDataPoint('rootevent', $event));
			if(!is_array($_results))
				$_results = array($_results);
			$results = array_merge($results, $_results);

			// Check to see if this was the command indicator, or if we were addressed by name ("!command" versus "Yukari: command")
			if($trigger == $indicator)
			{
				// Okay, this was a named command - we treat this as special, and dispatch another event for it.
				$_results = $dispatcher->trigger(\Yukari\Event\Instance::newEvent(sprintf('irc.input.namedcommand.%s', $command))
					->setDataPoint('command', $command)
					->setDataPoint('text', $text)
					->setDataPoint('target', $event['target'])
					->setDataPoint('hostmask', $event['hostmask'])
					->setDataPoint('is_private', false)
					->setDataPoint('rootevent', $event));
				if(!is_array($_results))
					$_results = array($_results);
				$results = array_merge($results, $_results);
			}
		}

		return $results;
	}

	/**
	 * Handle and interpret IRC response events, and fire off additional events for the individual response types.
	 * @param \Yukari\Event\Instance $event - The event to interpret.
	 * @return array - Returns an array of IRC output events to send.
	 */
	public function handleResponse(\Yukari\Event\Instance $event)
	{
		$dispatcher = Kernel::getDispatcher();
		$response_map = Kernel::get('core.response_map');

		$event_code = (int) $event['code'];
		$event_type = $response_map->getResponseType($event_code);

		// Just in case we wtf at a non-standard response code.
		if($event_type === false)
			return NULL;

		$results = $dispatcher->trigger(\Yukari\Event\Instance::newEvent(sprintf('irc.input.response.%s', $event_type))
			->setDataPoint('code', $event_code)
			->setDataPoint('description', $event['description'])
			->setDataPoint('rootevent', $event));

		return $results;
	}
}
