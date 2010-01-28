#!/usr/bin/php
<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 1
 * Copyright:	(c) 2009 - 2010 -- Failnet Project
 * License:		GNU General Public License - Version 2
 *
 *===================================================================
 *
 */

/**
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
 */

// Define some constants here for use later on
define('FAILNET_VERSION', '2.0.0A1');
define('FAILNET_ROOT', './');
define('FAILNET_DB_ROOT', FAILNET_ROOT . 'data/db/');

// Include our general functions file now -- We require it as it is always essential.
// It also has our autoloader function, so we kinda need that.  ;)
require FAILNET_ROOT . 'includes/functions.php';

// Set our autoload function.
spl_autoload_register('failnet_load_file');

// Check to see if we are even on the minimum PHP version necessary.
if(version_compare('5.2.3', PHP_VERSION, '>'))
	throw_fatal('Failnet requires PHP version 5.2.3 or better, while the currently installed PHP version is ' . PHP_VERSION);

// Load Failnet up!
$failnet = new failnet_core();

// Run Failnet
$failnet->run();

