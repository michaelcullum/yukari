<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     event
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

namespace Yukari\Event;
use Yukari\Kernel;

/**
 * Yukari - Event dispatcher object,
 * 	    Used to provide listener registration for handling of events, to surpass the antiquated plugins system.
 *
 *
 * @category    Yukari
 * @package     event
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Dispatcher
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
	 * @return \Yukari\Event\Dispatcher - Provides a fluent interface.
	 */
	public function register($event_type, $listener, array $listener_params = array())
	{
		if(!isset($this->listeners[$event_type]) || !is_array($this->listeners[$event_type]))
		{
			$this->listeners[$event_type] = array();
		}

		array_push($this->listeners[$event_type], array(
			'listener'		=> $listener,
			'params'		=> $listener_params,
		));

		return $this;
	}

	/**
	 * Register a new listener with the dispatcher, inserting the listener to the top of the stack for the event
	 * @param string $event_type - The type of event type to attach the listener to.
	 * @param callable $listener - The callable reference for the listener.
	 * @param array $listener_params - Any extra parameters to pass to the listener.
	 * @return \Yukari\Event\Dispatcher - Provides a fluent interface.
	 */
	public function preRegister($event_type, $listener, array $listener_params = array())
	{
		if(!isset($this->listeners[$event_type]) || !is_array($this->listeners[$event_type]))
		{
			$this->listeners[$event_type] = array();
		}

		array_unshift($this->listeners[$event_type], array(
			'listener'		=> $listener,
			'params'		=> $listener_params,
		));

		return $this;
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
	 * @param \Yukari\Event\Instance $event - The event to dispatch.
	 * @return array - Array of returned information from each listener.
	 */
	public function trigger(\Yukari\Event\Instance $event)
	{
		if(!$this->hasListeners($event->getName()))
		{
			return;
		}

		$results = array();
		foreach($this->listeners[$event->getName()] as $listener)
		{
			$result = call_user_func_array($listener['listener'], array_merge(array($event), $listener['params']));

			if($result === false)
			{
				break;
			}
			elseif($result !== NULL && $result !== true)
			{
				if(is_array($result))
				{
					$results = array_merge($results, $result);
				}
				else
				{
					$results[] = $result;
				}
			}
		}

		return $results;
	}

	/**
	 * Dispatch an event to registered listeners, purified so that boolean values can be returned (and only null values are ignored)
	 * @param \Yukari\Event\Instance $event - The event to dispatch.
	 * @return array - Array of returned information from each listener.
	 */
	public function cleanTrigger(\Yukari\Event\Instance $event)
	{
		if(!$this->hasListeners($event->getName()))
		{
			return;
		}

		$results = array();
		foreach($this->listeners[$event->getName()] as $listener)
		{
			$result = call_user_func_array($listener['listener'], array_merge(array($event), $listener['params']));
			if($result !== NULL)
			{
				$results[] = $result;
			}
		}

		return $results;
	}
}
