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
 * Failnet - Channel residence tracking plugin,
 * 		Used to track what channels Failnet is in. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_plugin_channels extends failnet_plugin_common
{
	public function cmd_response()
	{
		if($this->event instanceof failnet_event_response)
		{
			switch($this->event->code)
			{
				case failnet_event_response::RPL_ENDOFNAMES:
					// Joined a new channel, let's track it.
					$chanargs = explode(' ', $this->event->arguments);
					$this->failnet->chans[] = $chanargs[1];
					if($this->failnet->speak)  
						$this->call_privmsg($chanargs[1], $this->failnet->get('intro_msg'));
						// Only do the intro message if we're allowed to speak.
				break;
			}
		}
		else // If this isn't a response, it HAS to be a request.
		{
			switch($this->event->type)
			{
				case failnet_event_request::TYPE_PART:
					if($this->event->nick != $this->failnet->get('nick'))
						return;
				break;

				case failnet_event_request::TYPE_KICK:
					if($this->event->get_arg('user') != $this->failnet->get('nick'))
						return;
				break;
				
				default:
					return;
				break;
			}
			foreach($this->failnet->chans as $key => $channel)
			{
				if($channel == $this->event->get_arg('channel'))
				{
					unset($this->failnet->chans[$key]);
					return;
				}
			}
		}
	}
}

?>