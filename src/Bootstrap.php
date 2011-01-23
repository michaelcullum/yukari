<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
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
 */

namespace Yukari;

// Set the root path
define('Yukari\ROOT_PATH', (defined('Yukari\RUN_PHAR')) ? YUKARI : 'phar://' . YUKARI_PHAR);

// Absolute essentials first
require Yukari\ROOT_PATH . '/Constants.php';
require Yukari\ROOT_PATH . '/Exception.php';

/**
 * We need to start checking to see if the requirements for Yukari have been met
 *
 * Things we check:
 *  - PHP_SAPI
 *  - PDO availability
 *  - PDO+SQlite availability
 */
if(strtolower(PHP_SAPI) !== 'cli')
	throw new \RuntimeException('Yukari must be run in the CLI SAPI');
if(!extension_loaded('PDO'))
	throw new \RuntimeException('Yukari requires PDO');
if(!extension_loaded('pdo_sqlite'))
	throw new \RuntimeException('Yukari requires the SQLite PDO extension');

// Load up the common files, and get going.
require Yukari\ROOT_PATH . '/Bot.php';
require Yukari\ROOT_PATH . '/Functions.php';
require Yukari\ROOT_PATH . '/Autoload.php';
require Yukari\ROOT_PATH . '/Environment.php';

// Set our error and exception handlers
@set_error_handler('Yukari\\errorHandler');
// @set_exception_handler('Yukari\\exceptionHandler'); // @todo uncomment when an exception handler is written

// Check to see if date.timezone is empty in the PHP.ini; if so, set the timezone with some Hax to prevent strict errors.
if(!ini_get('date.timezone'))
	@date_default_timezone_set(@date_default_timezone_get());

// Run indefinitely...
set_time_limit(0);

// The first chunk always gets in the way, so we drop it.
array_shift($_SERVER['argv']);

$environment = new Yukari\Environment();
$environment->runBot();
