<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0
 * SVN ID:		$Id$
 * Copyright:	(c) 2009 - Failnet Project
 * License:		http://opensource.org/licenses/gpl-2.0.php  |  GNU Public License v2
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
 * Failnet - Reminder plugin,
 * 		A plugin to have Failnet remind users about various events in X minutes or X hours Y minutes. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_plugin_reminder extends failnet_plugin_common
{
	public $enable = true;
	public $reminders = array();
	
	public function connect()
	{
		$table_exists = $this->failnet->db->query('SELECT COUNT(*) FROM sqlite_master WHERE name = ' . $this->failnet->db->quote('remind'))->fetchColumn();
		if(!$table_exists)
		{
			display('=== Creating reminders table...');
			$this->failnet->db->exec(file_get_contents(FAILNET_ROOT . 'includes/schemas/remind.sql'));
		}
		display(' - Loading reminders...');
		// @todo Use failnet_plugin_reminder::load_all() to load the entire list of reminders into memory
	}
	
	public function tick()
	{
		// @todo Check here for any reminders that need to be processed, 
		//			even if reminders are disabled, because we need to clear out
		// 			the expired reminders anyways.
	}
	
	public function cmd_privmsg()
	{
		// @todo process requests for new reminders here
	}
	
	public function load_all()
	{
		// @todo totally load the DB of reminders here
	}
	
	public function remove()
	{
		// @todo remove a specific reminder here
	}
	
	public function add()
	{
		// @todo add a reminder here
	}
	
	public function deliver()
	{
		// @todo Remind a user about something here!
	}
}

?>