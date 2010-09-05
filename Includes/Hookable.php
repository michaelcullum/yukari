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
 * Failnet - Hook base class,
 * 	    Used as the base class that will handle method hooking.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
abstract class Hookable extends Base
{
	/**
	 * @var boolean - Does this object support hooks?
	 */
	public $supports_hooks = true;

	/**
	 * @var boolean - If this object supports hooks, are they enabled?
	 */
	public $using_hooks = false;

	/**
	 * @var string - The current class name
	 */
	public static $__CLASS__ = __CLASS__;

	/**
	 * __call hook enabler, intercepts calls to methods and checks for hooks, then forwards the call to the actual method.
	 * @param string $name - Method name
	 * @param array $arguments - Method parameters
	 * @return void
	 *
	 * @throws Failnet\HookableException
	 */
	public function __call($name, $arguments)
	{
		$hook = Failnet\Bot::getObject('core.hook');
		if(method_exists($this, "_$name"))
		{
			$hook_ary = $hook->retrieveHook(get_class($this), $name);
			if(!empty($hook_ary))
			{
				foreach($hook_ary as $call)
				{
					// process the hook data here
					switch($call['type'])
					{
						case Failnet\HOOK_OVERRIDE:
							return call_user_func_array($call['hook_call'], $arguments);
						break;

						case Failnet\HOOK_STACK:
							call_user_func_array($call['hook_call'], $arguments);
						break;

						case Failnet\HOOK_LAMBDA:
							$call['hook_call']($arguments);
						break;
					}
				}
			}
			return call_user_func_array(array($this, "_$name"), $arguments);
		}
		else
		{
			throw new HookableException(sprintf('Call to undefined method - %2$s::%1$s', $name, get_class($this)), HookableException::ERR_HOOKABLE_UNDEFINED_METHOD_CALL);
		}
	}

	/**
	 * __callStatic hook enabler, intercepts static calls to methods and checks for hooks, then forwards the static call to the actual method.
	 * @param string $name - Method name
	 * @param array $arguments - Method parameters
	 * @return void
	 *
	 * @throws Failnet\HookableException
	 */
	public function __callStatic($name, $arguments)
	{
		$hook = Failnet\Bot::getObject('core.hook');
		if(method_exists(static::$__CLASS__, "_$name"))
		{
			$hook_ary = $hook->retrieveHook(static::$__CLASS__, $name);
			if(!empty($hook_ary))
			{
				foreach($hook_ary as $call)
				{
					// process the hook data here
					switch($call['type'])
					{
						case Failnet\HOOK_OVERRIDE:
							return call_user_func_array($call['hook_call'], $arguments);
						break;

						case Failnet\HOOK_STACK:
							call_user_func_array($call['hook_call'], $arguments);
						break;

						case Failnet\HOOK_LAMBDA:
							$call['hook_call']($arguments);
						break;
					}
				}
			}
			return call_user_func_array(array(static::$__CLASS__, "static::_$name"), $arguments);
		}
		else
		{
			throw new HookableException(sprintf('Call to undefined method - %2$s::%1$s', $name, static::$__CLASS__), HookableException::ERR_HOOKABLE_UNDEFINED_METHOD_CALL);
		}
	}
}
