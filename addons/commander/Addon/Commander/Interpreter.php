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
	 * @return void
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
			$text = array_pad(explode(' ', $event['text'], 2), 2, '');

			$_results = $dispatcher->trigger(\Yukari\Event\Instance::newEvent(null, sprintf('irc.input.command.%s', $text[0]))
				->setDataPoint('command', $text[0])
				->setDataPoint('text', $text[1])
				->setDataPoint('hostmask', $event['hostmask'])
				->setDataPoint('rootevent', $event));
			$results = array_merge($results, $_results);

			$_results = $dispatcher->trigger(\Yukari\Event\Instance::newEvent(null, sprintf('irc.input.privatecommand.%s', $text[0]))
				->setDataPoint('command', $text[0])
				->setDataPoint('text', $text[1])
				->setDataPoint('hostmask', $event['hostmask'])
				->setDataPoint('rootevent', $event));
			$results = array_merge($results, $_results);
		}
		elseif(preg_match('#^(' . preg_quote($indicator, '#') . '|' . preg_quote($our_name, '#') . ': )([a-z0-9]+)( .*)?#i', $event['text'], $matches))
		{
			// Make sure we have a full array here.
			list($trigger, $command, $text) = array_pad($matches, 3, '');

			$_results = $dispatcher->trigger(\Yukari\Event\Instance::newEvent(null, sprintf('irc.input.command.%s', $command))
				->setDataPoint('command', $command)
				->setDataPoint('text', $text)
				->setDataPoint('hostmask', $event['hostmask'])
				->setDataPoint('rootevent', $event));
			$results = array_merge($results, $_results);

			// Check to see if this was the command indicator, or if we were addressed by name ("!command" versus "Yukari: command")
			if(substr($trigger, 0, strlen($indicator)) != $indicator)
			{
				// Okay, this was a named command - we treat this as special, and dispatch another event for it.
				$_results = $dispatcher->trigger(\Yukari\Event\Instance::newEvent(null, sprintf('irc.input.namedcommand.%s', $command))
					->setDataPoint('command', $command)
					->setDataPoint('text', $text)
					->setDataPoint('hostmask', $event['hostmask'])
					->setDataPoint('rootevent', $event));
				$results = array_merge($results, $_results);
			}
		}

		return $results;
	}
}
