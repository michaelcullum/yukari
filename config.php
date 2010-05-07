<?php
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
 * Failnet Configuration File
 *
 * Here be dragons!
 */

return array(

// Server settings
	'use_ssl'			=> false,
	'server'			=> 'irc.freenode.net',
	'port'				=> 6667,
	'nick'				=> 'Failnet',
	'user'				=> 'Failnet',
	'name'				=> 'Failnet',

// Nodes to load
	'nodes_list'		=> array(
		'server', // node
		'help', // node
		'authorize', // node
		'ignore', // node
	),

// Plugins to automatically load on startup.
	'plugin_list'		=> array(
		'server',
        'ping',
		'nickserv',
		'help',
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

// Should the bot say things in channel, or directly message users instead?
// @todo implement this properly
	'speak'				=> true,

// How long should Failnet wait after the last recieved event to ping the server to check the connection?
	'ping_wait'			=> 300,

// How long after a server ping is sent will we assume that the connection is lost?
	'ping_timeout'		=> 120,

// How many messages should be stored in the log queue before the queue is written to the file?
	'log_queue'			=> 60,

// List of IRC channels to automatically join if autojoin plugin is loaded.
	'autojoins'			=> array(
		'#failnet',
	),

// Prefix for commands directed at Failnet.
	'cmd_prefix'		=> '|',

// Should Failnet join a channel when invited?
	'join_on_invite'	=> false,

// Should Failnet automatically rejoin a channel when kicked?
	'autorejoin'		=> false,

// Standard messages for Failnet.
	'intro_msg'			=> 'Let there be faiiiillll!',
	'part_msg'			=> 'Bai baiiii!',
	'restart_msg'		=> 'ZOMG, BRB!',
	'dai_msg'			=> 'OH SHI--',
	'quit_msg'			=> 'Failnet PHP IRC Bot',

// What output level should we be in?
//		OUTPUT_SILENT		- Do not output anything.
//		OUTPUT_NORMAL		- Only output standard in/out data (IRC messages and actions)
//		OUTPUT_DEBUG		- Output warnings and notices in addition to standard data
//		OUTPUT_DEBUG_FULL	- Output warnings, notices, standard data, and also show event triggers
//		OUTPUT_RAW			- Output raw IRC data going in and out.
	'output'			=> OUTPUT_NORMAL,
);
