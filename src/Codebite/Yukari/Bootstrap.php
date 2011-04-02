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
define('Codebite\\Yukari\\ROOT_PATH', (\Codebite\Yukari\RUN_PHAR === true) ? 'phar://' . YUKARI_PHAR : YUKARI . '/src');
define('OpenFlame\\ROOT_PATH', \Codebite\Yukari\ROOT_PATH);

/**
 * We need to start checking to see if the requirements for Yukari have been met
 *
 * Things we check:
 *  - PHP_SAPI
 */
if(strtolower(PHP_SAPI) !== 'cli')
{
	throw new \RuntimeException('Yukari must be run in the CLI SAPI');
}

// Define a few constants on startup here.
define('Codebite\\Yukari\\BASE_MEMORY', memory_get_usage());
define('Codebite\\Yukari\\BASE_MEMORY_PEAK', memory_get_peak_usage());
define('Codebite\\Yukari\\START_TIME', time());
define('Codebite\\Yukari\\START_MICROTIME', microtime(true));

// Absolute essentials first
require \Codebite\Yukari\ROOT_PATH . '/OpenFlame/Framework/Autoloader.php';
require \Codebite\Yukari\ROOT_PATH . '/Codebite/Yukari/Kernel.php';
require \Codebite\Yukari\ROOT_PATH . '/Codebite/Yukari/Functions.php';
require \Codebite\Yukari\ROOT_PATH . '/Codebite/Yukari/Environment.php';

// Set our error and exception handlers
@set_error_handler('Codebite\\Yukari\\errorHandler');
@set_exception_handler('Codebite\\Yukari\\exceptionHandler');

// Check to see if date.timezone is empty in the PHP.ini; if so, set the timezone with some Hax to prevent strict errors.
if(!ini_get('date.timezone'))
{
	@date_default_timezone_set(@date_default_timezone_get());
}

// Run indefinitely...
set_time_limit(0);

// The first chunk always gets in the way, so we drop it.
array_shift($_SERVER['argv']);

\Codebite\Yukari\Kernel::load();
\Codebite\Yukari\Kernel::initEnvironment();
\Codebite\Yukari\Kernel::getEnvironment()->runBot();