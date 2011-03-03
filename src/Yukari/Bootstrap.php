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

// Set the root path
define('Yukari\\ROOT_PATH', (\Yukari\RUN_PHAR === true) ? 'phar://' . YUKARI_PHAR : YUKARI . '/src');

/**
 * We need to start checking to see if the requirements for Yukari have been met
 *
 * Things we check:
 *  - PHP_SAPI
 */
if(strtolower(PHP_SAPI) !== 'cli')
	throw new \RuntimeException('Yukari must be run in the CLI SAPI');

// Define a few constants on startup here.
define('Yukari\\BASE_MEMORY', memory_get_usage());
define('Yukari\\BASE_MEMORY_PEAK', memory_get_peak_usage());
define('Yukari\\START_TIME', time());
define('Yukari\\START_MICROTIME', microtime(true));

// Absolute essentials first
require \Yukari\ROOT_PATH . '/Yukari/Autoloader.php';
require \Yukari\ROOT_PATH . '/Yukari/Kernel.php';
require \Yukari\ROOT_PATH . '/Yukari/Functions.php';
require \Yukari\ROOT_PATH . '/Yukari/Environment.php';

// Set our error and exception handlers
@set_error_handler('Yukari\\errorHandler');
@set_exception_handler('Yukari\\exceptionHandler');

// Check to see if date.timezone is empty in the PHP.ini; if so, set the timezone with some Hax to prevent strict errors.
if(!ini_get('date.timezone'))
	@date_default_timezone_set(@date_default_timezone_get());

// Include the Symfony Yaml component
/*
require \Yukari\ROOT_PATH . '/vendor/Symfony/Yaml/Yaml.php';
require \Yukari\ROOT_PATH . '/vendor/Symfony/Yaml/Exception.php';
require \Yukari\ROOT_PATH . '/vendor/Symfony/Yaml/Parser.php';
require \Yukari\ROOT_PATH . '/vendor/Symfony/Yaml/ParserException.php';
require \Yukari\ROOT_PATH . '/vendor/Symfony/Yaml/Inline.php';
*/

// Run indefinitely...
set_time_limit(0);

// The first chunk always gets in the way, so we drop it.
array_shift($_SERVER['argv']);

\Yukari\Kernel::load();
\Yukari\Kernel::initEnvironment();
\Yukari\Kernel::getEnvironment()->runBot();
