<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2010 -- Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet\Core;
use Failnet as Root;
use Failnet\Bot as Bot;

/**
 * Failnet - Event dispatcher object,
 * 	    Used to provide listener registration for handling of events, to surpass the antiquated plugins system.
 *
 *
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Dispatcher extends Root\Base
{
	/**
	 * @var array - Our array of stored listeners and any extra data.
	 */
	protected $listeners = array();

	/**
	 * Register a new listener with the dispatcher
	 * @param string $event_type - The type of event type to attach the listener to.
	 * @param callable $listener - The callable reference for the listener.
	 * @param array $listener_params - Any extra parameters to pass to the listener.
	 * @return void
	 */
	public function register($event_type, $listener, array $listener_params = array())
	{
		$this->listeners[$event_type][hash('md5', (string) $listener)] = array(
			'listener'		=> $listener,
			'params'		=> $listener_params,
		);
	}

	/**
	 * Drop a listener from the dispatcher
	 * @param string $event_type - The type of event to remove the listener from.
	 * @param callable $listener - The callable reference to identify the listener with.
	 * @return void
	 */
	public function unregister($event_type, $listener)
	{
		if(!$this->hasListeners($event_type))
			return;

		unset($this->listeners[$event_type][hash('md5', (string) $listener)]);
	}

	/**
	 * Check to see if an event has any listeners registered to it
	 * @param string $event_type - The type of event to check.
	 * @return boolean - Does the event have listeners attached?
	 */
	public function hasListeners($event_type)
	{
		return !empty($this->listeners[$event_type]);
	}

	/**
	 * Dispatch an event to registered listeners
	 * @param Failnet\Event\EventBase $event - The event to dispatch.
	 * @return void
	 */
	public function dispatch(Failnet\Event\EventBase $event)
	{
		if(!$this->hasListeners($event->getType()))
			return;

		foreach($this->listeners[$event->getType()] as $listener)
		{
			call_user_func_array($listener['listener'], array_merge(array($event), $listener['params']));
		}
	}
}
