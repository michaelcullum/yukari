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
 * @link        http://github.com/Obsidian1510/Failnet3
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Failnet\User;
use Failnet as Root;
use Failnet\Bot as Bot;
use Failnet\Lib as Lib;

/**
 * Failnet - Session interface,
 *      Prototype that defines methods that session objects must implement.
 *
 *
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
class Session extends Root\Hookable implements SessionInterface
{
	public function __construct(Failnet\Lib\Hostmask $hostmask)
	{
		// asdf
	}

	public function login($password)
	{
		// asdf
	}

	public function logout()
	{
		// asdf
	}

	public function getLastActive()
	{
		// asdf
	}

	public function setLastActive($time)
	{
		// asdf
	}

	public function onDestroy()
	{
		// asdf
	}
}
