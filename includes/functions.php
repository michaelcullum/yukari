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
if(!defined('IN_FAILNET')) exit(1);

/**
 * Echos a message, and cleans out any extra NL's after the message.
 * Also will echo an array of messages properly as well.
 * 
 * @param mixed $msg - The message or messages we want to echo to the terminal. 
 */
function display($msg)
{
	if(is_array($msg))
	{
		foreach($msg as $line)
		{
			$line = (preg_match('/' . PHP_EOL . '(| )$/i', $line)) ? substr($line, 0, strlen($line) - 1) : $line;
			echo $line . PHP_EOL;		
		}
	}
	else
	{
		$msg = (preg_match('/' . PHP_EOL . '(| )$/i', $msg)) ? substr($msg, 0, strlen($msg) - 1) : $msg;
		echo $msg . PHP_EOL;
	}
}

/**
 * Shell for Failnet's built-in error handler class.
 * 
 * @param $errno
 * @param $msg_text
 * @param $errfile
 * @param $errline
 */
function fail_handler($errno, $msg_text, $errfile, $errline)
{
	global $failnet;
	return $failnet->error->fail($errno, $msg_text, $errfile, $errline);
}

/**
* Return formatted string for filesizes
* 
* @author (c) 2007 phpBB Group 
* 
* @param integer $bytes - The number of bytes to convert.
* 
* @return string - The filesize converted into KiB, MiB, or GiB.
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
 *
 * @param int $time The time/integer to calulate the values from
 * @return string
 */
function timespan($time, $last_comma = false)
{
	$return = array();

	$days = floor($time / 86400);
	if ($days > 0)
	{
		$return[] = $days . (($days == 1) ? ' day ' : ' days');
		$time %= 86400;
	}

	$hours = floor($time / 3600);
	if ($hours > 0)
	{
		$return[] = $hours . (($hours == 1) ? ' hour' : ' hours');
		$time %= 3600;
	}

	$minutes = floor($time / 60);
	if ($minutes > 0)
	{
		$return[] = $minutes . (($minutes == 1) ? ' minute' : ' minutes');
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

?>