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

namespace Codebite\Yukari\Addon\DataTracking;
use Codebite\Yukari\Kernel;

/**
 * Yukari - User tracking object,
 *      Populates and maintains a local cache of user hostmasks, and each user's channel modes in the channels that the bot is inhabiting.
 *
 *
 * @category    Yukari
 * @package     addon
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class UserTracker
{
	/**
	 * Register the listeners we need for this addon to work properly.
	 * @return \Codebite\Yukari\Addon\DataTracker\UserTracker - Provides a fluent interface.
	 */
	public function registerListeners()
	{
		$dispatcher = Kernel::getDispatcher();
                $dispatcher->register('irc.input.join', array(Kernel::get('addon.usertracker'), 'trackUserJoin'));

		return $this;
	}
}
