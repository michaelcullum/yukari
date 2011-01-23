<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     lib
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

namespace Failnet\Lib;

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
 * @note reserves 301xx error codes
 */
class JSONException extends Failnet\FailnetException
{
	const ERR_JSON_NO_FILE = 30100;
	const ERR_JSON_UNKNOWN = 30101;
	const ERR_JSON_NO_ERROR = 30102;
	const ERR_JSON_DEPTH = 30103;
	const ERR_JSON_CTRL_CHAR = 30104;
	const ERR_JSON_SYNTAX = 30105;
}
