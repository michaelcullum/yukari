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
define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
set_include_path(get_include_path() . PATH_SEPARATOR . FAILNET_ROOT);

/**
 * Check to make sure the CLI SAPI is being used...
 */
if (strtolower(PHP_SAPI) != 'cli')
{
	echo 'Failnet must be run in the CLI SAPI';
    exit(1);
}

/**
 * Check to see if date.timezone is empty in the PHP.ini, if so, set the default timezone to prevent strict errors.
 */
if (!ini_get('date.timezone')) date_default_timezone_set(date_default_timezone_get());

if(file_exists(FAILNET_ROOT . 'data/restart')) unlink(FAILNET_ROOT . 'data/restart');
set_time_limit(0);

// STOPPED REWRITE HERE...
// @note: Includes!

$failnet = new failnet();

// Begin printing info to the terminal window with some general information about Failnet.
echo failnet::HR . failnet::NL;
echo 'Failnet -- PHP-based IRC Bot version ' . FAILNET_VERSION . ' - $Revision$' . failnet::NL;
echo 'Copyright: (c) 2009 - Obsidian' . failnet::NL;
echo 'License: http://opensource.org/licenses/gpl-2.0.php' . failnet::NL;
echo 'Failnet uses code from PHPBot [ Copyright (c) 2009 Kai Tamkun ]' . failnet::NL;
echo failnet::HR . failnet::NL;
echo 'Failnet is starting up. Go get yourself a coffee.' . failnet::NL;

// Set error handler
echo '- Loading error handler' . failnet::NL; 

function fail_handler($errno, $msg_text, $errfile, $errline)
{
	global $failnet;
	return $failnet->error->fail($errno, $msg_text, $errfile, $errline);
}

@set_error_handler('fail_handler');

// Loading DBs, initializing some vars
$actions = array_flip(file('data/actions'));

// Load dictionary file - This fails on Windows systems.
echo '- Loading dictionary (if file is present on OS)' . failnet::NL; $dict = (@file_exists('/etc/dictionaries-common/words')) ? file('/etc/dictionaries-common/words') : array();

// Load user DB
echo '- Loading user database' . failnet::NL; $failnet->loaduserdb();

// Adding the core to the modules list and loading help file
$failnet->modules[] = 'core';
$help['core'] = 'Good luck.'; //file_get_contents('data/corehelp'); // This file was just...missing.  O_o

// Load modules
$load = array(
	'simple_html_dom',
	'warfare',
	'slashdot',
	'xkcd',
	'reload',
/*
	'dict',
	'alchemy',
	'notes',
	'markov',
*/
);
echo '- Loading modules' . failnet::NL;
foreach($load as $item)
{
	if(include 'modules/' . $item . '.php') echo '=-= Loaded "' . $item . '" module' . failnet::NL;
}

// This is a hack to allow us to restart Failnet if we're running the script through a batch file.
echo '- Removing termination indicator file' . failnet::NL; if(file_exists('data/restart')) unlink('data/restart');

// Load in the configuration data file
echo '- Loading configuration file for specified IRC server' . failnet::NL; $failnet->load($argv[1]);

echo '- Loading ignored users list' . failnet::NL; $failnet->ignore = explode(', ', file_get_contents('data/ignore_users'));

// In case of restart/reload, to prevent 'Nick already in use' (which asplodes everything)
echo 'Preparing to connect...' . failnet::NL; sleep(2);

// Initiate the beast!  Run, Failnet, RUN!
echo 'Failnet loaded and ready!' . failnet::NL;
echo 'Connecting to server...' . failnet::NL;
$failnet->run();

