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
 * Failnet - Log handling plugin,
 * 		Used to log conversation, events, and whatnot occurring with the server. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_plugin_log extends failnet_plugin_common
{	
	public function cmd_response()
	{
		switch($this->event->type)
		{
			case failnet_event_request::TYPE_KICK:
				$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === ' . $this->event->nick . ' has kicked user ' . $this->event->get_arg('user') . ' from ' . $this->event->get_arg('channel') . (($this->event->get_arg('comment')) ? ' : ' . $this->event->get_arg('comment') : ''));
			break;

			case failnet_event_request::TYPE_JOIN:
				$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === ' . $this->event->nick . ' has joined ' . $this->event->get_arg('channel'));
			break;

			case failnet_event_request::TYPE_PART:
				$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === ' . $this->event->nick . ' has left ' . $this->event->get_arg('channel') . (($this->event->get_arg('message')) ? ' : ' . $this->event->get_arg('message') : ''));
			break;

			case failnet_event_request::TYPE_QUIT:
				$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === ' . $this->event->nick . ' has quit' . (($this->event->get_arg('message')) ? ' : ' . $this->event->get_arg('message') : ''));
			break;

			case failnet_event_request::TYPE_TOPIC:
				$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === ' . $this->event->nick . ' has changed the topic in ' . $this->event->get_arg('channel') . ' to ' . $this->event->get_arg('topic'));
			break;

			case failnet_event_request::TYPE_MODE:  // @todo Finish this one.  This requires counting the args.
				
			break;

			case failnet_event_request::TYPE_NOTICE:
				$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === Notice from ' . $this->event->nick . ' : ' . $this->event->get_arg('text'));
			break;

			case failnet_event_request::TYPE_PRIVMSG:
			case failnet_event_request::TYPE_ACTION:
				$this->failnet->log->log($this->event->get_arg((($this->event->type == failnet_event_request::TYPE_PRIVMSG) ? 'text' : 'action')), $this->event->nick, $this->event->get_arg((($this->event->type == failnet_event_request::TYPE_PRIVMSG) ? 'reciever' : 'target')));
			break;
		}
	}
	
	public function post_dispatch()
	{
		// @todo In here, record what WE did.
	}
}

?>