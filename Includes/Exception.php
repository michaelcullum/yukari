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
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 * @note reserves 110xx error codes
 */
class AutoloadException extends FailnetException
{
	const ERR_AUTOLOAD_CLASS_INVALID = 11000;
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
 * @note reserves 120xx error codes
 */
class EnvironmentException extends FailnetException
{
	const ERR_ENVIRONMENT_LOAD_FAILED = 12000;
	const ERR_ENVIRONMENT_NO_SUCH_OBJECT = 12001;
	const ERR_ENVIRONMENT_FAILED_CONFIG_LOAD = 12002;
	const ERR_ENVIRONMENT_CONFIG_MISSING = 12003;
	const ERR_ENVIRONMENT_UNSUPPORTED_CONFIG = 12004;
	const ERR_ENVIRONMENT_NO_ACCESS_CFG_DIR = 12005;
	const ERR_ENVIRONMENT_NO_ACCESS_DB_DIR = 12006;
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
 * @note reserves 130xx error codes
 */
class HookableException extends FailnetException
{
	const ERR_HOOKABLE_UNDEFINED_METHOD_CALL = 13000;
}
