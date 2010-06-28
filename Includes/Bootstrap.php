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
 *  - DB dir being usable
 */
if(strtolower(PHP_SAPI) !== 'cli')
	throw new Exception(ex(Exception::ERR_STARTUP_PHP_SAPI));
if(!extension_loaded('PDO'))
	throw new Exception(ex(Exception::ERR_STARTUP_NO_PDO));
if(!extension_loaded('pdo_sqlite'))
	throw new Exception(ex(Exception::ERR_STARTUP_NO_PDO_SQLITE));
if(!file_exists(FAILNET_ROOT . 'Data/Config/') || !is_readable(FAILNET_ROOT . 'Data/Config/') || !is_writeable(FAILNET_ROOT . 'Data/Config/') || !is_dir(FAILNET_ROOT . 'Data/Config/'))
	throw new Exception(ex(Exception::ERR_STARTUP_NO_ACCESS_CFG_DIR));
if(!file_exists(FAILNET_ROOT . 'Data/DB/') || !is_readable(FAILNET_ROOT . 'Data/DB/') || !is_writeable(FAILNET_ROOT . 'Data/DB/') || !is_dir(FAILNET_ROOT . 'Data/DB/'))
	throw new Exception(ex(Exception::ERR_STARTUP_NO_ACCESS_DB_DIR));

// Check to see if date.timezone is empty in the PHP.ini; if so, set the timezone with some Hax to prevent strict errors.
if(!ini_get('date.timezone'))
	@date_default_timezone_set(@date_default_timezone_get());

// The first chunk always gets in the way, so we drop it.
array_shift($_SERVER['argv']);

// Load up the common files, setup our JIT class autoloading, and get going.
require FAILNET_ROOT . 'Includes/Base.php';
require FAILNET_ROOT . 'Includes/Bot.php';
require FAILNET_ROOT . 'Includes/Autoload.php';
require FAILNET_ROOT . 'Includes/Functions.php';

Autoload::register();
Bot::loadArgs($_SERVER['argv']);
define('IN_INSTALL', (Bot::arg('mode') === 'install') ? true : false);
define('CONFIG_FILE', (Bot::arg('config') ? Bot::arg('config') : 'Config'));

// Load the appropriate core file.
if(!IN_INSTALL && file_exists(FAILNET_ROOT . 'Data/Config/' . CONFIG_FILE . '.php'))
{
	Bot::setCore('core', 'Failnet\\Core\\Core');
}
else
{
	Bot::setCore('core', 'Failnet\\Install\\Core');
}

// Run it!
Bot::core()->run();
