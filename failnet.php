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
define('FAILNET_ROOT', './');

// Let's grab some essential files, first
require FAILNET_ROOT . 'includes/constants.php';
require FAILNET_ROOT . 'includes/common.php';
require FAILNET_ROOT . 'includes/autoload.php';
require FAILNET_ROOT . 'includes/functions.php';

// Setup the JIT class autoloading.
failnet_autoload::register();

// Check to see if we are even on the minimum PHP version necessary.
if(version_compare('5.3.0', PHP_VERSION, '>'))
	throw_fatal('Failnet ' . FAILNET_VERSION . ' requires PHP version 5.3.0 or better, while the currently installed PHP version is ' . PHP_VERSION);

// Load the Failnet core
failnet::setCore('core', 'failnet_core');

// Run Failnet
failnet::core()->run();
