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
 */

namespace Failnet;

/**
 * @ignore
 */

// Absolute essentials first
require FAILNET_ROOT . 'Includes/Constants.php';
require FAILNET_ROOT . 'Includes/Exception.php';

/**
 * We need to start checking to see if the requirements for Failnet can be met
 *
 * Things we check:
 *  - PHP_SAPI
 *  - PDO availability
 *  - PDO+SQlite availability
 */
if(strtolower(PHP_SAPI) !== 'cli')
	throw new StartupException('Failnet must be run in the CLI SAPI', StartupException::ERR_STARTUP_PHP_SAPI);
if(!extension_loaded('PDO'))
	throw new StartupException('Failnet requires the PDO PHP extension to be loaded', StartupException::ERR_STARTUP_NO_PDO);
if(!extension_loaded('pdo_sqlite'))
	throw new StartupException('Failnet requires the SQLite PDO extension to be loaded', StartupException::ERR_STARTUP_NO_PDO_SQLITE);

// Load up the common files, and get going.
require FAILNET_ROOT . 'Includes/Bot.php';
require FAILNET_ROOT . 'Includes/Functions.php';
require FAILNET_ROOT . 'Includes/Base.php';
require FAILNET_ROOT . 'Includes/Hookable.php';
require FAILNET_ROOT . 'Includes/Autoload.php';
require FAILNET_ROOT . 'Includes/Environment.php';

// Set our error and exception handlers
@set_error_handler('Failnet\\errorHandler');
// @set_exception_handler('Failnet\\exceptionHandler'); // @todo uncomment when an exception handler is written

$environment = new Failnet\Environment();
$environment->runBot();
