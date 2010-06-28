<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     Failnet
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

namespace Failnet;

/**
 * Failnet - Base class,
 * 	    Used as the base class that will handle method hooking.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
abstract class Base
{
	/**
	 * @var string - The current class name
	 */
	public static $__CLASS__ = __CLASS__;

	/**
	 * __call hook enabler, intercepts calls to methods and checks for hooks, then forwards the call to the actual method.
	 * @param string $name - Method name
	 * @param array $arguments - Method parameters
	 * @return void
	 * @throws Failnet\Exception
	 */
	public function __call($name, $arguments)
	{
		if(method_exists($this, "_$name"))
		{
			$hook_ary = Bot::retrieveHook(get_class($this), $name);
			if(!empty($hook_ary))
			{
				foreach($hook_ary as $hook)
				{
					// process the hook data here
					if($hook['type'] === HOOK_OVERRIDE)
					{
						return call_user_func_array($hook['hook_call'], $arguments);
					}
					elseif($hook['type'] === HOOK_STACK)
					{
						call_user_func_array($hook['hook_call'], $arguments);
					}
				}
			}
			return call_user_func_array(array($this, "_$name"), $arguments);
		}
		else
		{
			throw new Exception(ex(Exception::ERR_UNDEFINED_METHOD_CALL, array($name, get_class($this))));
		}
	}

	/**
	 * __callStatic hook enabler, intercepts static calls to methods and checks for hooks, then forwards the static call to the actual method.
	 * @param string $name - Method name
	 * @param array $arguments - Method parameters
	 * @return void
	 * @throws Failnet\Exception
	 */
	public function __callStatic($name, $arguments)
	{
		if(method_exists(static::$__CLASS__, "_$name"))
		{
			$hook_ary = Bot::retrieveHook(static::$__CLASS__, $name);
			if(!empty($hook_ary))
			{
				foreach($hook_ary as $hook)
				{
					// process the hook data here
					if($hook['type'] === HOOK_OVERRIDE)
					{
						return call_user_func_array($hook['hook_call'], $arguments);
					}
					elseif($hook['type'] === HOOK_STACK)
					{
						call_user_func_array($hook['hook_call'], $arguments);
					}
				}
			}
			return call_user_func_array(array(static::$__CLASS__, "static::_$name"), $arguments);
		}
		else
		{
			throw new Exception(ex(Exception::ERR_UNDEFINED_METHOD_CALL, array($name, static::$__CLASS__)));
		}
	}
}
