#!/usr/bin/php
<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version		2.1.0 DEV
 * @category	Failnet
 * @package		Failnet
 * @author		Failnet Project
 * @copyright	(c) 2009 - 2010 -- Failnet Project
 * @license		http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 * @link		http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-2.0.php>.
 *
 */


/**
 * @ignore
 */
define('FAILNET_ROOT', __DIR__);

// Absolute essentials first
require FAILNET_ROOT . 'includes/constants.php';
require FAILNET_ROOT . 'includes/exception.php';

/**
 * We need to start checking to see if the requirements for Failnet can be met
 *
 * Things we check:
 *  - Minimum PHP version
 *  - PHP_SAPI
 *  - PDO availability
 *  - PDO+SQlite availability
 *  - DB dir accessibility
 */
if(version_compare(FAILNET_MIN_PHP, PHP_VERSION, '>'))
	throw new failnet_exception(failnet_exception::ERR_STARTUP_MIN_PHP);
if(strtolower(PHP_SAPI) != 'cli')
	throw new failnet_exception(failnet_exception::ERR_STARTUP_PHP_SAPI);
if(!extension_loaded('PDO'))
	throw new failnet_exception(failnet_exception::ERR_STARTUP_NO_PDO);
if(!extension_loaded('pdo_sqlite'))
	throw new failnet_exception(failnet_exception::ERR_STARTUP_NO_PDO_SQLITE);
if(!file_exists(FAILNET_ROOT . 'data/db/') || !is_readable(FAILNET_ROOT . 'data/db/') || !is_writeable(FAILNET_ROOT . 'data/db/') || !is_dir(FAILNET_ROOT . 'data/db/'))
	throw new failnet_exception(failnet_exception::ERR_STARTUP_NO_ACCESS_DB_DIR);

// Load up the common files, setup our JIT class autoloading, and get going.
require FAILNET_ROOT . 'includes/common.php';
require FAILNET_ROOT . 'includes/autoload.php';
require FAILNET_ROOT . 'includes/functions.php';

failnet_autoload::register();
failnet::setCore('core', 'failnet_core');
failnet::core()->run();
