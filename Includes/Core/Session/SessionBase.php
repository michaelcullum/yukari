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

namespace Failnet\Core\Session;
use Failnet as Root;
use Failnet\Bot as Bot;
use Failnet\Lib as Lib;

class SessionBase implements SessionInterface
{
	public $acl;

	public $hostmask;

	public $pointer = '';

	public $session_key = '';

	public function __construct(Lib\Hostmask $hostmask, $session_key, $pointer)
	{
		list($this->hostmask, $this->session_key, $this->pointer) = array($hostmask, $session_key, $pointer);
	}
}

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
interface SessionInterface extends \ArrayAccess
{
	public function __construct(Lib\Hostmask $hostmask, $session_key, $pointer);
	public function login($password);
	public function logout();
	public function getLastActive();
	public function setLastActive($time);
	public function onDestroy();
}
