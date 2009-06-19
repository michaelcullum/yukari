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

/**
 * Failnet Configuration File
 */

// Here be dragons!
return array(
	'server'		=> 'irc.freenode.net',
	'port'			=> 6667,
	'nick'			=> 'Failnet',
	'user'			=> 'Failnet',
	'user'			=> 'Failnet',

// Nickserv identify password. ;)
	'pass'			=> 'somepasswordhere',
	'server_pass'	=> '',
	'owner'			=> 'Desdenova',
	'debug'			=> false,

	'altnicks'		=> array(
		'Failnet_',
		'Failnet__',
		'Failnet-',
		'Failnet--',
		'Failnet-_',
	),
	
// Standard messages for Failnet.
	'intro_msg'			=> 'Let there be faiiiillll!',
	'restart_msg'		=> 'ZOMG, BRB!',
	'dai_msg'			=> 'OH SHI--',
	'quit_msg'			=> 'Failnet PHP IRC Bot',
);

?>