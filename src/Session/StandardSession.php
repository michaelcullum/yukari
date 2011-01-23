<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     session
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

namespace Failnet\Session;
use Failnet\Bot as Bot;
use Failnet\Lib as Lib;

/**
 * Failnet - Session interface,
 *      Prototype that defines methods that session objects must implement.
 *
 *
 * @category    Yukari
 * @package     session
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class StandarSession extends SessionBase implements SessionInterface
{
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
