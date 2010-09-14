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
	 * @var array - Our array of stored listeners and their data.
	 */
	protected $listeners = array();

	// @todo document
	public function register($event_type, $listener, array $listener_params = array())
	{
		$this->listeners[$event_type][] = array(
			'listener'		=> $listener,
			'params'		=> $listener_params,
		);
	}

	public function unregister($event_type, $listener)
	{
		if(!$this->hasListeners($event_type))
			return;

		foreach($this->listeners[$event_type] as $key => $entry)
		{
			if($entry['listener'] === $listener)
				unset($this->listeners[$event_type][$key]);
		}
	}

	public function hasListeners($event_type)
	{
		return !empty($this->listeners[$event_type]);
	}

	public function dispatch(Failnet\Event\EventBase $event)
	{
		foreach($this->listeners[$event->getType()] as $listener)
		{
			call_user_func_array($listener['listener'], array_merge(array($event), $listener['params']));
		}
	}
}
