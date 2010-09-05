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
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
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
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
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
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 * @note reserves 10xx error codes
 */
class StartupException extends FailnetException
{
	const ERR_STARTUP_MIN_PHP = 1000;
	const ERR_STARTUP_PHP_SAPI = 1001;
	const ERR_STARTUP_NO_PDO = 1002;
	const ERR_STARTUP_NO_PDO_SQLITE = 1003;
	const ERR_STARTUP_NO_ACCESS_CFG_DIR = 1004;
	const ERR_STARTUP_NO_ACCESS_DB_DIR = 1005;
}

/**
 * Failnet - Subordinate exception class
 *      Extension of the Failnet exception class.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 * @note reserves 11xx error codes
 */
class AutoloadException extends FailnetException
{
	const ERR_AUTOLOAD_CLASS_INVALID = 1100;
}

/**
 * Failnet - Subordinate exception class
 *      Extension of the Failnet exception class.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 * @note reserves 12xx error codes
 */
class EnvironmentException extends FailnetException
{
	const ERR_ENVIRONMENT_LOAD_FAILED = 1200;
	const ERR_ENVIRONMENT_NO_SUCH_OBJECT = 1201;
	const ERR_ENVIRONMENT_FAILED_CONFIG_LOAD = 1202;
	const ERR_ENVIRONMENT_UNSUPPORTED_CONFIG = 1203;
}

/**
 * Failnet - Subordinate exception class
 *      Extension of the Failnet exception class.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 * @note reserves 13xx error codes
 */
class HookableException extends FailnetException
{
	const ERR_HOOKABLE_UNDEFINED_METHOD_CALL = 1300;
}
