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
 * Failnet Configuration File
 * 
 * Here be dragons!
 */

return array(

// Server settings
	'server'			=> 'irc.freenode.net',
	'port'				=> 6667,
	'nick'				=> 'Failnet',
	'user'				=> 'Failnet',
	'name'				=> 'Failnet',

// Plugins to automatically load on startup. 
	'plugin_list'		=> array(
		'admin',
		'authorize',
		'autojoin',
		'channels',
		'ignore',
		'log',
		'moderator',
		'nickserv',
		'ping',
		'pong',
	),

// What is the nickname service bot? If there isn't, leave this as an empty string.
	'nickbot'			=> 'nickserv',
	
// Nickname service identify password. ;)
	'pass'				=> 'somepasswordhere',

// Server password, if necessary.
	'server_pass'		=> '',

// The nick of the Bot's owner.
	'owner'				=> 'Obsidian',

// Something that every command that the bot recieves will be prefixed with
	'cmd_prefix'		=> '|',

// Should the bot say stuff in in response to channel conversation, or stay silent?
	'speak'				=> true,

// Should the bot stop running through factoids after the first factoid it finds?
	'single_factoid'	=> false,

// Should the bot join a channel on invite?
	'joininvite'		=> false, 

// Should the bot attept to autorejoin a channel on kick?
	'autorejoin'		=> false,

// How long should Failnet wait after the last recieved event to ping the server to check the connection?
	'ping_wait'			=> 120,

// How long after a server ping is sent will we assume that the connection is lost?
	'ping_timeout'		=> 10,

// How many messages should be stored in the log queue before the queue is written to the file?
	'log_queue'			=> 60,

// List of alternate IRC nicknames to use
	'altnicks'			=> array(
		'Failnet_',
		'Failnet__',
		'Failnet-',
		'Failnet--',
		'Failnet-_',
	),

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