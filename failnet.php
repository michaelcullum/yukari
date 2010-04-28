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

// Let's grab the config file first
require FAILNET_ROOT . 'includes/constants.php';

// Check to see if we are even on the minimum PHP version necessary.
if(version_compare(FAILNET_MIN_PHP, PHP_VERSION, '>'))
	throw new Exception('Failnet ' . FAILNET_VERSION . ' requires PHP ' . FAILNET_MIN_PHP . ' or better, while the currently installed PHP version is ' . PHP_VERSION);

// Check to make sure the CLI SAPI is being used...
if(strtolower(PHP_SAPI) != 'cli')
	throw new Exception('Failnet must be run in the CLI SAPI');

// Make sure that PDO and the SQLite PDO extensions are loaded, we need them.
if(!extension_loaded('PDO'))
	throw new Exception('Failnet requires the PDO PHP extension to be loaded');
if(!extension_loaded('pdo_sqlite'))
	throw new Exception('Failnet requires the PDO_SQLite PHP extension to be loaded');

// Make sure our database directory actually exists and is manipulatable
if(!file_exists(FAILNET_ROOT . 'data/db/') || !is_readable(FAILNET_ROOT . 'data/db/') || !is_writeable(FAILNET_ROOT . 'data/db/') || !is_dir(FAILNET_ROOT . 'data/db/'))
	throw new Exception('Failnet requires the database directory to exist and be readable/writeable');

// Load up the common files and get going then.
require FAILNET_ROOT . 'includes/common.php';
require FAILNET_ROOT . 'includes/autoload.php';
require FAILNET_ROOT . 'includes/functions.php';

// Setup the JIT class autoloading.
failnet_autoload::register();

// Load the Failnet core
failnet::setCore('core', 'failnet_core');

// Run Failnet
failnet::core()->run();
