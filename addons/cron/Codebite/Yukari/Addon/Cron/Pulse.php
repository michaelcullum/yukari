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

namespace Codebite\Yukari\Addon\Cron;
use Codebite\Yukari\Kernel;
use \OpenFlame\Framework\Event\Instance as Event;

/**
 * Yukari - Cron regulated dispatcher object,
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
	protected $last = array();

	protected $irc_last = array();

	protected $interval = array(
		'second'	=> 1,
		'minute'	=> 60,
		'hour'		=> 3600,
		'day'		=> 86400,
		'month'		=> 2592000, // 30 days,
	);

	/**
	 * Register the listeners we need for this addon to work properly.
	 * @return \Codebite\Yukari\Addon\Commander\Interpreter - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		Kernel::registerListener('yukari.startup', -5, array($this, 'handleStartup'));
		Kernel::registerListener('irc.connect', -5, array($this, 'handleIRCConnect'));

		Kernel::registerListener('yukari.tick', -5, array($this, 'handleGlobalTick'));
		Kernel::registerListener('irc.tick', -3, array($this, 'handleIRCTick'));

		return $this;
	}

	public function handleStartup(Event $event)
	{
		$now = time();
		foreach($this->interval as $type => $seconds)
		{
			$this->last[$type] = $now + $seconds;
		}
	}

	public function handleIRCConnect(Event $event)
	{
		$now = time();
		$network = $event->get('network');
		foreach($this->interval as $type => $seconds)
		{
			$this->irc_last[$network][$type] = $now + $seconds;
		}
	}

	public function handleGlobalTick(Event $event)
	{
		$now = time();
		foreach($this->last as $type => $time)
		{
			if($now < $time)
			{
				return;
			}

			Kernel::trigger(Event::newEvent('cron.pulse.' . $type));

			$this->last[$type] = $now + $this->interval[$type];
		}
	}

	public function handleIRCTick(Event $event)
	{
		$now = time();
		$network = $event->get('network');
		foreach($this->irc_last[$network] as $type => $time)
		{
			if($now < $time)
			{
				return;
			}

			Kernel::trigger(Event::newEvent('cron.pulse.' . $type));

			$this->irc_last[$network][$type] = $now + $this->interval[$type];
		}
	}
}
