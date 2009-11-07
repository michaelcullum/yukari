<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 1
 * Copyright:	(c) 2009 - Failnet Project
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


/**
 * Failnet Configuration File
 * 
 * Here be dragons!
 */

return array(

// Server settings
	'transport'			=> 'tcp',
	'server'			=> 'irc.freenode.net',
	'port'				=> 6667,
	'nick'				=> 'Failnet',
	'user'				=> 'Failnet',
	'name'				=> 'Failnet',

// Nodes to load
	'nodes_list'		=> array(
		'socket',
		'irc',
		'log',
		'error',
		'auth',
		'ignore',
	),

// Plugins to automatically load on startup. 
	'plugin_list'		=> array(
		'server',
        'ping',
		'nickserv',
		'auto',
		'admin',
		'authorize',
		'ignore',
		'log',
		'weather',
		'tools',
		'offense',
	),

// What is the nickname service bot? If there isn't, leave this as an empty string.
	'nickbot'			=> 'NickServ',
	
// Nickname service identify password. ;)
	'pass'				=> 'somepasswordhere',

// Server password, if necessary.
	'server_pass'		=> '',

// The nick of the Bot's owner.
	'owner'				=> 'Obsidian',

// Should the bot say stuff in in response to channel conversation, or stay silent?
	'speak'				=> true,

// How long should Failnet wait after the last recieved event to ping the server to check the connection?
	'ping_wait'			=> 120,

// How long after a server ping is sent will we assume that the connection is lost?
	'ping_timeout'		=> 10,

// How many messages should be stored in the log queue before the queue is written to the file?
	'log_queue'			=> 60,

// List of IRC channels to automatically join if autojoin plugin is loaded.
	'autojoins'			=> array(
		'#failnet',
	),

// Standard messages for Failnet.
	'intro_msg'			=> 'Let there be faiiiillll!',
	'part_msg'			=> 'Bai baiiii!',
	'restart_msg'		=> 'ZOMG, BRB!',
	'dai_msg'			=> 'OH SHI--',
	'quit_msg'			=> 'Failnet PHP IRC Bot',
	
// Should we be in debug mode?
	'debug'				=> false,
);

?>