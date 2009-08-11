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
 * Failnet - Nickserv automatic identification plugin,
 * 		If enabled in the config, on end of MOTD we send an identify message to the nickname services bot to identify. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_plugin_nickserv extends failnet_plugin_common
{
	public function cmd_response()
	{
		if($this->event->code !== failnet_event_response::ERR_NICKNAMEINUSE)
			return;

		// If someone else is using our nick, let's GHOST them out of it.  :)
		if($this->failnet->get('nickbot'))
		{
			$this->call_privmsg($this->failnet->get('nickbot'), 'GHOST ' . $this->failnet->get('nick') . ' ' . $this->failnet->get('pass'));
		}
	}
	
	public function cmd_notice()
	{
		// Check to see if nickserv is asking for authentication, and if so then we'll give it
		if(strtolower($this->event->nick) != strtolower($this->failnet->get('nickbot')))
			return;

		if(preg_match('#^.*nickname is (registered|owned)#i', $this->event->get_arg(1)))
		{
			if(is_null($this->failnet->get('pass')) || !$this->failnet->get('pass'))
				$this->call_privmsg($this->failnet->get('nickbot'), 'IDENTIFY ' . $this->failnet->get('pass'));
		}
		elseif(preg_match('#^.*' . $this->failnet->get('nick') . '.* has been killed#i', $this->event->get_arg(1)))
		{
			$this->call_nick($this->failnet->get('nick'));
		}
	}
	
	public function cmd_nick()
	{
		// If this is our nick being changed, we should react and change it internally.
		if($this->event->nick == $this->failnet->get('nick'))
			$this->failnet->nick = $this->event->get_arg('nick');
	}
}

?>