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
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
	 * @throws failnet_exception
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
	 * @throws failnet_exception
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
