<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     session
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

namespace Failnet\Session;
use Failnet as Root;
use Failnet\Bot as Bot;
use Failnet\Lib as Lib;

/**
 * Failnet - Session interface,
 *      Prototype that defines methods that session objects must implement.
 *
 *
 * @category    Failnet
 * @package     session
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
interface SessionInterface extends \ArrayAccess
{
	public function __construct(Lib\Hostmask $hostmask, $session_id, $pointer);
	public function flash($flash_key, $flash_value = NULL);
	public function getHostmask();
	public function getLastActive();
	public function setLastActive($time);
	public function onDestroy();
}
