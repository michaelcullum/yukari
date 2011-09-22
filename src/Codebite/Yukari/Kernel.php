<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @category    Yukari
 * @package     Yukari
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

namespace Codebite\Yukari;
use \OpenFlame\Framework\Core;
use \OpenFlame\Framework\Dependency\Injector;

/**
 * Yukari - Kernel class,
 *      Used as the static master class that will provides easy access to the Yukari environment.
 *
 *
 * @category    Yukari
 * @package     Yukari
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Kernel extends \OpenFlame\Framework\Core
{
	/**
	 * @var integer - This bot's build number.
	 */
	protected static $build;

	/**
	 * Get the build number for this version of Yukari
	 * @return string - The build string for this version of Yukari.
	 */
	public static function getBuildNumber()
	{
		if(self::$build === NULL)
		{
			if(file_exists(\Codebite\Yukari\ROOT_PATH . '/VERSION'))
			{
				self::$build = sprintf('build_%d', file_get_contents(\Codebite\Yukari\ROOT_PATH . '/VERSION'));
			}
			else
			{
				self::$build = 'build_DEV';
			}

		}

		return self::$build;
	}

	/**
	 * Get a currently-stored object.
	 * @param string $slot - The slot to look in.
	 * @return mixed - NULL if the slot specified is unused, or the object present in the slot specified.
	 */
	public static function get($slot)
	{
		$injector = Injector::getInstance();

		return $injector->get($slot);
	}

	/**
	 * Store an object in the kernel.
	 * @param string $slot - The slot to store the object in.
	 * @param object $object - The object to store.
	 * @return object - The object just set.
	 */
	public static function set($slot, $object)
	{
		return self::setObject($slot, $object);
	}

	/**
	 * Shortcut method for dispatching an event.
	 * @param \OpenFlame\Framework\Event\Instance $event - The event to dispatch.
	 * @param integer $type - The dispatch type to use
	 * @return \OpenFlame\Framework\Event\Instance - The event dispatched.
	 */
	public static function trigger(\OpenFlame\Framework\Event\Instance $event, $type = \OpenFlame\Framework\Event\Dispatcher::TRIGGER_NOBREAK)
	{
		return self::get('dispatcher')->trigger($event, $type);
	}

	/**
	 * Shortcut method to register a new event listener
	 * @param string $name - The event name to register to.
	 * @param integer $priority - The priority to set the listener as.
	 * @param callable $listener - The listener to trigger.
	 * @return \OpenFlame\Framework\Event\Dispatcher - The event dispatcher.
	 */
	public static function registerListener($name, $priority, $listener)
	{
		return self::get('dispatcher')->register($name, $priority, $listener);
	}
}
