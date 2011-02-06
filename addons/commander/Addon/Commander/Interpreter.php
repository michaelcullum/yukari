<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
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
		// asdf
	}
}
