<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     connection
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
 * Yukari - Connection manager class,
 * 	    Used as the IRC connection instance manager.
 *
 *
 * @category    Yukari
 * @package     connection
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Manager
{
	public $socket;

	protected $name = '';

	protected $network = '';

	public function __construct($network)
	{
		$seeder = Kernel::get('seeder');

		$this->network = (string) $network;
		$this->name = $seeder->buildRandomString(12, (string) $network);
	}

	public function getNetwork()
	{
		return $this->network;
	}

	public function get($option)
	{
		return Kernel::getConfig(sprintf('irc_%s.%s', $this->name, $option));
	}

	public function set($option, $value)
	{
		return Kernel::setConfig(sprintf('irc_%s.%s', $this->name, $option), $value);
	}

	public function connect()
	{
		$this->socket = Kernel::get('irc.socket')
			->setManager($this)
			->connect();
	}

	public function disconnect()
	{
		$this->socket->close();
	}

	public function tickHook(Event $tick)
	{
		$queue = array();

		if(!$this->socket)
		{
			$this->connect();
		}

		$_t = Kernel::trigger(Event::newEvent('irc.tick')
			->set('network', $this->network)
			->set('mname', $this->name));
		$queue = $_t->getReturns();

		$event = $this->socket->get();

		if($event)
		{
			Kernel::trigger($event->set('network', $this->network)
				->set('mname', $this->name));

			if($event->getReturns())
			{
				foreach($event->getReturns() as $send_stack)
				{
					if(is_array($send_stack))
					{
						$queue = array_merge($queue, $send_stack);
					}
					else
					{
						$queue = array_merge($queue, array($send_stack));
					}
				}
			}
		}

		if(!empty($queue))
		{
			foreach($queue as $send_event)
			{
				if(!($send_event instanceof Event) || substr($send_event->getName(), 0, 11) !== 'irc.output.')
				{
					continue;
				}

				Kernel::trigger(Event::newEvent('irc.predispatch')
					->set('event', $send_event));

				$this->socket->sendEvent($send_event);

				Kernel::trigger(Event::newEvent('irc.postdispatch')
					->set('event', $send_event));
			}
		}
	}
}
