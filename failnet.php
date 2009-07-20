#!/usr/bin/php
<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0
 * SVN ID:		$Id$
 * Copyright:	(c) 2009 - Obsidian
 * License:		http://opensource.org/licenses/gpl-2.0.php  |  GNU Public License v2
 *
 *===================================================================
 *
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://opensource.org/licenses/gpl-2.0.php>.
 */

/**
 * @ignore
 */
define('IN_FAILNET', true);
define('FAILNET_VERSION', '2.0.0');
define('FAILNET_ROOT', realpath('.') . DIRECTORY_SEPARATOR);
define('FAILNET_DB_ROOT', realpath('.') . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR);
define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));

// Include our general functions file now -- We require it as it is always essential.
require FAILNET_ROOT . 'includes/functions.' . PHP_EXT;

// Check to see if we are even on the minimum PHP version necessary
if(version_compare('5.2.3', PHP_VERSION, '>'))
{
	if(file_exists(FAILNET_ROOT . 'data/restart')) 
		unlink(FAILNET_ROOT . 'data/restart');
	display(array('[Fatal Error] Failnet requires PHP version 5.2.3 or better.', 'Currently installed PHP version: ' . PHP_VERSION));
	sleep(3);
	exit(1);
}

// Load autoloader and register it
require FAILNET_ROOT . 'autoload.' . PHP_EXT;
failnet_autoload::register();

// Load Failnet up!
$failnet = new failnet_core();

// Run Failnet
$failnet->run();
?>