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
 * @copyright   (c) 2009 - 2011 Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Codebite\Yukari;
use \OpenFlame\Framework\Autoloader;
use \OpenFlame\Framework\Event\Instance as Event;
use \Codebite\Yukari\Kernel;

// Set the root path
define('Codebite\\Yukari\\ROOT_PATH', (\Codebite\Yukari\RUN_PHAR === true) ? 'phar://' . YUKARI_PHAR : YUKARI . '/src');

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

// Check to see if date.timezone is empty in the PHP.ini; if so, set the timezone with some Hax to prevent strict errors.
if(!ini_get('date.timezone'))
{
	@date_default_timezone_set(@date_default_timezone_get());
}

// Run indefinitely...
set_time_limit(0);

// Absolute essentials first
require \Codebite\Yukari\ROOT_PATH . '/OpenFlame/Framework/Autoloader.php';
Autoloader::register(\Codebite\Yukari\ROOT_PATH);

// Set our error and exception handlers
@set_error_handler('Codebite\\Yukari\\errorHandler');
@set_exception_handler('Codebite\\Yukari\\exceptionHandler');

// Get our injectors
require \Codebite\Yukari\ROOT_PATH . '/Codebite/Yukari/Injectors.php';
require \Codebite\Yukari\ROOT_PATH . '/Codebite/Yukari/Functions.php';

// this will need changed

$daemon = Kernel::get('yukari.daemon');
$dispatcher = Kernel::get('dispatcher');
$dispatcher->trigger(Event::newEvent('yukari.init'));
$dispatcher->trigger(Event::newEvent('yukari.exec'));
