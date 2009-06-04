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
if(!defined('IN_FAILNET')) return;

// Display a message...this is here to shrink the code and allow the right NL to always be used.
function display($msg)
{
	if(is_array($msg))
	{
		foreach($msg => $line)
		{
			echo $msg . failnet::NL;			
		}
	}
	else
	{
		echo $msg . failnet::NL;
	}
}

// This is a shell for the error handler class built into Failnet. 
function fail_handler($errno, $msg_text, $errfile, $errline)
{
	global $failnet;
	return $failnet->error->fail($errno, $msg_text, $errfile, $errline);
}

?>