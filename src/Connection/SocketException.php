<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     connection
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

namespace Failnet\Connection;
use Failnet\Bot as Bot;

/**
 * Failnet - Subordinate exception class
 *      Extension of the Failnet exception class.
 *
 *
 * @category    Yukari
 * @package     Yukari
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 * @note reserves 202xx error codes
 */
class SocketException extends Failnet\FailnetException
{
	const ERR_SOCKET_UNSUPPORTED_TRANSPORT = 20200;
	const ERR_SOCKET_ERROR = 20201;
	const ERR_SOCKET_FGETS_FAILED = 20202;
	const ERR_SOCKET_NO_CONNECTION = 20203;
	const ERR_SOCKET_SEND_UNSENDABLE_EVENT = 20204;
}
