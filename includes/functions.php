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
 * Copyright:	(c) 2009 - Failnet Project
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
if(!defined('IN_FAILNET')) exit(1);

/**
 * Class autoloading function, takes in a class name and parses it according to built-in rules.
 * 		Function will automatically strip out the failnet_ prefix if present.
 * 		If the class contains underscores, the autoload function will expect the underscores to be slashes for directories.
 * 		Example being if you load in "failnet_plugin_admin", it will attempt to load the file at /includes/plugins/admin.php
 * @param string $name - Class name to load
 * @return void
 */
function __autoload($name)
{
	// Begin by cleaning the class name of any possible ../. hacks
	$name = basename($name);

	// Now, drop the failnet_ prefix if it is there
	$name = (substr($class, 0, 8) == 'failnet_') ? substr($class, 8) : $class;

	// Replace any underscores with slashes...
	$name = str_replace('_', DIRECTORY_SEPARATOR, $name);
	
	// Now, we try to get the file.
	require FAILNET_ROOT . 'includes' . DIRECTORY_SEPARATOR . $name . '.' . PHP_EXT;
}

/**
 * Echos a message, and cleans out any extra NL's after the message.
 * 		Also will echo an array of messages properly as well.
 * @param mixed $msg - The message or messages we want to echo to the terminal. 
 * @return void
 */
function display($msg)
{
	if(is_array($msg))
	{
		foreach($msg as $line)
		{
			$line = (strrpos($line, PHP_EOL . PHP_EOL) !== false) ? substr($line, 0, strlen($line) - 1) : $line;
			echo $line . PHP_EOL;		
		}
	}
	else
	{
		$msg = (strrpos($log, PHP_EOL . PHP_EOL) !== false) ? substr($msg, 0, strlen($msg) - 1) : $msg;
		echo $msg . PHP_EOL;
	}
}

/**
 * Shell for Failnet's built-in error handler class.
 * @param $errno - Error number
 * @param $msg_text - Error message text
 * @param $errfile - Where was the error in?
 * @param $errline - What line was the error?
 * @return mixed
 */
function fail_handler($errno, $msg_text, $errfile, $errline)
{
	global $failnet;
	return $failnet->error->fail($errno, $msg_text, $errfile, $errline);
}

/**
* Return formatted string for filesizes
* @param integer $bytes - The number of bytes to convert.
* @return string - The filesize converted into KiB, MiB, or GiB.
* 
* @author (c) 2007 phpBB Group 
*/
function get_formatted_filesize($bytes)
{
	if ($bytes >= pow(2, 30))
		return round($bytes / 1024 / 1024 / 1024, 2) . ' GiB';

	if ($bytes >= pow(2, 20))
		return round($bytes / 1024 / 1024, 2) . ' MiB';

	if ($bytes >= pow(2, 10))
		return round($bytes / 1024, 2) . ' KiB';

	return $bytes . ' B';
}

/**
 * Converts a given integer/timestamp into days, minutes and seconds
 * @param integer $time - The time/integer to calulate the values from
 * @param boolean $last_comma - Should we have a comma between the second to last item of the list and the last, if more than 3 items for time? 
 * 									This WAS actually something of debate, for grammar reasons. :P
 * @return string
 */
function timespan($time, $last_comma = false)
{
	$return = array();

	$count = floor($time / 29030400);
	if ($count > 0)
	{
		$return[] = $count . (($count == 1) ? ' year' : ' years');
		$time %= 29030400;
	}

	$count = floor($time / 2419200);
	if ($count > 0)
	{
		$return[] = $count . (($count == 1) ? ' month' : ' months');
		$time %= 2419200;
	}

	$count = floor($time / 604800);
	if ($count > 0)
	{
		$return[] = $count . (($count == 1) ? ' week' : ' weeks');
		$time %= 604800;
	}

	$count = floor($time / 86400);
	if ($count > 0)
	{
		$return[] = $count . (($count == 1) ? ' day' : ' days');
		$time %= 86400;
	}

	$count = floor($time / 3600);
	if ($count > 0)
	{
		$return[] = $count . (($count == 1) ? ' hour' : ' hours');
		$time %= 3600;
	}

	$count = floor($time / 60);
	if ($count > 0)
	{
		$return[] = $count . (($count == 1) ? ' minute' : ' minutes');
		$time %= 60;
	}

	$uptime = (sizeof($return) ? implode(', ', $return) : '');

	if(!$last_comma)
	{
		if ($time > 0 || count($return) <= 0)
			$uptime .= (sizeof($return) ? ' and ' : '') . ($time > 0 ? $time : '0') . (($time == 1) ? ' second' : ' seconds');
	}
	else
	{
		if ($time > 0 || count($return) <= 0)
			$uptime .= (sizeof($return) ? ((sizeof($return) > 1) ? ',' : '') . ' and ' : '') . ($time > 0 ? $time : '0') . (($time == 1) ? ' second' : ' seconds');
	}

	return $uptime;
}

/**
 * Converts a delimited string of hostmasks into a regular expression that will match any hostmask in the original string.
 * @param array $list - Array of hostmasks
 * @return string - Regular expression
 * 
 * @author Phergie Development Team {@link http://code.assembla.com/phergie/subversion/nodes}
 */
function hostmasks_to_regex($list)
{
	static $hmask_find, $hmask_repl;
	if(empty($hmask_find))
		$hmask_find = array('\\', '^', '$', '.', '[', ']', '|', '(', ')', '?', '+', '{', '}');
	if(empty($hmask_repl))
		$hmask_repl = array('\\\\', '\\^', '\\$', '\\.', '\\[', '\\]', '\\|', '\\(', '\\)', '\\?', '\\+', '\\{', '\\}');

	$patterns = array();

	foreach($list as $hostmask)
	{
		// Find out which chars are present in the config mask and exclude them from the regex match
		$excluded = '';
		if (strpos($hostmask, '!') !== false)
		{
			$excluded .= '!';
		}
		if (strpos($hostmask, '@') !== false)
		{
			$excluded .= '@';
		}

		// Escape regex meta characters
		$hostmask = str_replace($hmask_find, $hmask_repl, $hostmask);

		// Replace * so that they match correctly in a regex
		$patterns[] = str_replace('*', ($excluded === '' ? '.*' : '[^' . $excluded . ']*'), $hostmask);
	}

	return ('#^' . implode('|', $patterns) . '$#is');
}

/**
 * Parses a IRC hostmask and sets nick, user and host bits.
 * @param string $hostmask - Hostmask to parse
 * @param string &$nick - Container for the nick
 * @param string &$user - Container for the username
 * @param string &$host - Container for the hostname
 * @return void
 * 
 * @author Phergie Development Team {@link http://code.assembla.com/phergie/subversion/nodes}
 */
function parse_hostmask($hostmask, &$nick, &$user, &$host)
{
	if (preg_match('/^([^!@]+)!([^@]+)@(.*)$/', $hostmask, $match) > 0)
	{
		list(, $nick, $user, $host) = array_pad($match, 4, NULL);
	}
	else
	{
		$host = $hostmask;
	}
}

?>