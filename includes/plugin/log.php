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
 * License:		GNU General Public License - Version 2
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
	public function cmd_join()
	{
		display(date('h:i') . ' ' . $this->event->nick . ' has joined ' . $this->event->get_arg('channel'));
		$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === ' . $this->event->nick . ' has joined ' . $this->event->get_arg('channel'));
	}
	
	public function cmd_part()
	{
		display(date('h:i') . ' ' . $this->event->nick . ' has left ' . $this->event->get_arg('channel'));
		$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === ' . $this->event->nick . ' has left ' . $this->event->get_arg('channel') . (($this->event->get_arg('message')) ? ' : ' . $this->event->get_arg('message') : ''));
	}
	
	public function cmd_kick()
	{
		display(date('h:i') . ' ' . $this->event->nick . ' has kicked user ' . $this->event->get_arg('user') . ' from ' . $this->event->get_arg('channel') . (($this->event->get_arg('comment')) ? ' : ' . '[' . $this->event->get_arg('comment') . ']' : ''));
		$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === ' . $this->event->nick . ' has kicked user ' . $this->event->get_arg('user') . ' from ' . $this->event->get_arg('channel') . (($this->event->get_arg('comment')) ? ' : ' . '[' . $this->event->get_arg('comment') . ']' : ''));
	}
	
	public function cmd_quit()
	{
		display(date('h:i') . ' ' . $this->event->nick . ' has quit' . (($this->event->get_arg('message')) ? ' : ' . $this->event->get_arg('message') : ''));
		$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === ' . $this->event->nick . ' has quit' . (($this->event->get_arg('message')) ? ' : ' . $this->event->get_arg('message') : ''));
	}
	
	public function cmd_topic()
	{
		display(date('h:i') . ' ' . $this->event->nick . ' has changed the topic in ' . $this->event->get_arg('channel') . ' to ' . $this->event->get_arg('topic'));
		$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === ' . $this->event->nick . ' has changed the topic in ' . $this->event->get_arg('channel') . ' to ' . $this->event->get_arg('topic'));
	}
	
	public function cmd_mode()
	{
		$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === ' . $this->event->nick . ' has set mode ' . $this->event->get_arg('mode') . ' on ' . $this->event->get_arg('target'));
	}
	
	public function cmd_notice()
	{
		display(date('h:i') . ' [Notice] ' . $this->event->nick . ': ' . $this->event->get_arg('text'));
		$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === Notice from ' . $this->event->nick . ' : ' . $this->event->get_arg('text'));
	}
	
	public function cmd_privmsg()
	{
		display(date('h:i') . ' <' . $this->event->nick . (($this->event->fromchannel()) ? '/' . $this->event->arguments[0] : '') . '> ' . $this->event->get_arg('text'));
		$this->failnet->log->log($this->event->get_arg('text'), $this->event->nick, $this->event->get_arg('reciever'));
	}
	
	public function cmd_action()
	{
		display(date('h:i') . (($this->event->fromchannel()) ? '[' . $this->event->get_arg('reciever') . ']' : '') . ' *** ' . $this->event->nick . ' ' . $this->event->get_arg('action'));
		$this->failnet->log->log($this->event->get_arg('action'), $this->event->nick, $this->event->get_arg('target'));
	}
	
	public function post_dispatch(array $events)
	{
		// @todo In here, record what WE did.
	}
}

?>