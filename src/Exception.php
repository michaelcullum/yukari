<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
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
 * @todo branch out this class into several for each "package"
 *
 */

namespace Failnet;

/**
 * Failnet - Exception class,
 * 	    Extension of the default Exception class, adapted to suit Failnet's needs.
 *
 *
 * @category    Yukari
 * @package     Yukari
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class FailnetException extends \Exception
{
	const ERR_WTF = 0;
}

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
 * @note reserves 100xx error codes
 */
class StartupException extends FailnetException
{
	const ERR_STARTUP_MIN_PHP = 10000;
	const ERR_STARTUP_PHP_SAPI = 10001;
	const ERR_STARTUP_NO_PDO = 10002;
	const ERR_STARTUP_NO_PDO_SQLITE = 10003;
}

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
 * @note reserves 101xx error codes
 */
class AutoloadException extends FailnetException
{
	const ERR_AUTOLOAD_CLASS_INVALID = 10100;
}

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
 * @note reserves 102xx error codes
 */
class EnvironmentException extends FailnetException
{
	const ERR_ENVIRONMENT_LOAD_FAILED = 10200;
	const ERR_ENVIRONMENT_NO_SUCH_OBJECT = 10201;
	const ERR_ENVIRONMENT_FAILED_CONFIG_LOAD = 10202;
	const ERR_ENVIRONMENT_CONFIG_MISSING = 10203;
	const ERR_ENVIRONMENT_UNSUPPORTED_CONFIG = 10204;
	const ERR_ENVIRONMENT_NO_ACCESS_CFG_DIR = 10205;
	const ERR_ENVIRONMENT_NO_ACCESS_DB_DIR = 10206;
	const ERR_ENVIRONMENT_OPTION_NOT_SET = 10207;
	const ERR_ENVIRONMENT_EXTRA_FILE_LOAD_FAIL = 10208;
}
