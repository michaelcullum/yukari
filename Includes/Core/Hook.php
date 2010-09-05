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


/**
 * Failnet - Hook handling class,
 * 	    Used to handle hook registration.
 *
 *
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Hook extends Root\Base
{
	/**
	 * @var array - The hook data loaded.
	 */
	protected $hooks = array();

	/**
	 * Register a hook function to be called before
	 * @param array $hooked_method_call - The callback info for the method we're hooking onto.
	 * @param mixed $hook_call - The function/method to hook on top of the method we're hooking (accepts lambda functions).
	 * @param constant $hook_type - The type of hook we're using.
	 * @return boolean - Were we successful?
	 *
	 * @throws Failnet\HookException
	 */
	public static function registerHook($hooked_method_class, $hooked_method_name, $hook_call, $hook_type = HOOK_NULL)
	{
		// We're deliberately ignoring HOOK_NULL here.
		if(!in_array($hook_call, array(Root\HOOK_STACK, Root\HOOK_OVERRIDE, Root\HOOK_LAMBDA)))
			throw new Exception(ex(Exception::ERR_REGISTER_HOOK_BAD_HOOK_TYPE)); // @todo -> HookException

		// Check for unsupported classes
		if(substr($hooked_method_class, 0, 7) != 'Failnet')
			throw new Exception(ex(Exception::ERR_REGISTER_HOOK_BAD_CLASS, array($hooked_method_class))); // @todo HookException

		/**
		 * Hooks are placed into the hook info array using the following array structure:
		 *
			$this->hooks[$hooked_method_class][$hooked_method_name] = array(
				array(
					'hook_call'		=> $hook_call,
					'type'			=> HOOK_STACK,
				),
				array(
					'hook_call'		=> $hook_call,
					'type'			=> HOOK_OVERRIDE,
				),
			);
		 *
		 */

		/**
		 * At some point in the future, we may want to check to see if the method we are hooking onto exists,
		 * but for now we will not, as the class may not yet be loaded.
		 * We'll just have to take their word for it.
		 */
		$this->hooks[$hooked_method_class][$hooked_method_name][] = array('hook_call' => $hook_call, 'type' => $hook_type);
	}

	/**
	 * Checks to see if any hooks have been assigned to a designated class/method, and returns their info.
	 * @param string $hooked_method_class - The name of the class to check a method of for hooks
	 * @param string $hooked_method_name - The name of the previously specified class's method to check for hooks
	 * @return mixed - Returns either false if there's no such hooks associated, or returns the array containing that method's hook data.
	 */
	public static function getHook($hooked_method_class, $hooked_method_name)
	{
		if(!isset($this->hooks[$hooked_method_class][$hooked_method_name]))
			return false;
		return $this->hooks[$hooked_method_class][$hooked_method_name];
	}

	/**
	 * Aliases to Failnet\Core\Hook->getHook()
	 * @see Failnet\Core\Hook->getHook()
	 */
	public function __invoke($hooked_method_class, $hooked_method_name)
	{
		return $this->getHook($hooked_method_class, $hooked_method_name);
	}
}
