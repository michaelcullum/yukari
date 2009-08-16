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
 * Failnet - Autojoin plugin,
 * 		Upon end of MOTD, attempts to automatically join all channels listed in the config. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_plugin_autojoin extends failnet_plugin_common
{
	public function cmd_response()
	{
		switch ($this->event->code)
		{
			case failnet_event_response::RPL_ENDOFMOTD:
			case failnet_event_response::ERR_NOMOTD:
				$channels = $this->failnet->get('autojoins');
				if (!empty($channels))
				{
					foreach($channels as $channel)
					{
						$this->call_join($channel);
					}
				}
		}
	}
}

?>