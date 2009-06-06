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
	if(file_exists(FAILNET_ROOT . 'data/restart')) unlink(FAILNET_ROOT . 'data/restart');
	display('Failnet must be run in the CLI SAPI');
    exit(1);
}

/**
 * Check to see if date.timezone is empty in the PHP.ini, if so, set the default timezone to prevent strict errors.
 */
if (!ini_get('date.timezone')) date_default_timezone_set(date_default_timezone_get());
set_time_limit(0);

// Begin printing info to the terminal window with some general information about Failnet.
display(array(
	failnet::HR,
	'Failnet -- PHP-based IRC Bot version ' . FAILNET_VERSION . ' - $Revision$',
	'Copyright: (c) 2009 - Obsidian',
	'License: http://opensource.org/licenses/gpl-2.0.php',
	failnet::HR,
	'Failnet is starting up. Go get yourself a coffee.',
));

require(FAILNET_ROOT . 'includes/bootstrap.' . PHP_EXT);

// Set error handler
display('- Loading error handler'); @set_error_handler('fail_handler');

// Loading some DBs now, initializing some vars
$actions = array_flip(file('data/actions'));

// Load dictionary file - This fails on Windows systems.
display('- Loading dictionary (if file is present on OS)'); $dict = (@file_exists('/etc/dictionaries-common/words')) ? file('/etc/dictionaries-common/words') : array();

// Adding the core to the modules list and loading help file
$failnet->modules[] = 'core';
$help['core'] = 'For help with the core system, please reference this site: http://www.assembla.com/wiki/show/failnet/';

// Load modules
$load = array(
	'simple_html_dom',
	'warfare',
	'slashdot',
	'xkcd',
/*
	'alchemy',
	'notes',
*/
);
display('- Loading modules');
foreach($load as $item)
{
	if(include 'modules/' . $item . '.php') display('=-= Loaded "' . $item . '" module');
}

// This is a hack to allow us to restart Failnet if we're running the script through a batch file.
display('- Removing termination indicator file'); if(file_exists('data/restart')) unlink('data/restart');
display('- Loading user database'); $failnet->loaduserdb();
display('- Loading configuration file for specified IRC server'); $failnet->load($_SERVER['argv'][1]);
display('- Loading ignored users list'); $failnet->ignore->load(); // explode(', ', file_get_contents('data/ignore_users'));
display('Preparing to connect...'); sleep(1); // In case of restart/reload, to prevent 'Nick already in use' (which asplodes everything)
display(array('Failnet loaded and ready!', 'Connecting to server...'));

$failnet->run();

?>