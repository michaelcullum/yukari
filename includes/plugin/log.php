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
 * Failnet - Log handling plugin,
 * 		Used to log conversation, events, and whatnot occurring with the server. 
 * 
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - Failnet Project
 * @license GNU General Public License - Version 2
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

	public function cmd_invite()
	{
		display(date('h:i') . ' ' . $this->event->nick . ' has extended an invitation to  ' . $this->event->get_arg('channel'));
		$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === ' . $this->event->nick . ' has extended an invitation to ' . $this->event->get_arg('channel'));
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
		display(date('h:i') . ' ' . $this->event->nick . ' has set mode ' . $this->event->get_arg('mode') . ' ' . $this->event->get_arg('target') . ' ' . $this->event->get_arg('limit') . ' ' . $this->event->get_arg('user') . ' ' . $this->event->get_arg('banmask'));
		$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === ' . $this->event->nick . ' has set mode ' . $this->event->get_arg('mode') . ' ' . $this->event->get_arg('target') . ' ' . $this->event->get_arg('limit') . ' ' . $this->event->get_arg('user') . ' ' . $this->event->get_arg('banmask'));
	}
	
	public function cmd_notice()
	{
		display(date('h:i') . ' [Notice] ' . $this->event->nick . ': ' . $this->event->get_arg('text'));
		$this->failnet->log->add(date('D m/d/Y - h:i:s A') . ' - === Notice from ' . $this->event->nick . ' : ' . $this->event->get_arg('text'));
	}
	
	public function cmd_privmsg()
	{
		// Make sure we don't record passwords
		if(!preg_match('#^' . preg_quote($this->failnet->get('cmd_prefix'), '#') . '[[new|add|del|drop]user|login|auth|delconfirm|confirmdel|pass|setpass|[\+|\-|new|add|drop|del]access]#i', $this->event->get_arg('text')))
		{
			display(date('h:i') . ' <' . $this->event->nick . (($this->event->fromchannel()) ? '/' . $this->event->get_arg('receiver') : '') . '> ' . $this->event->get_arg('text'));
			$this->failnet->log->log($this->event->get_arg('text'), $this->event->nick, $this->event->get_arg('receiver'));
		}
	}
	
	public function cmd_action()
	{
		display(date('h:i') . (($this->event->fromchannel()) ? '[' . $this->event->get_arg('receiver') . ']' : '') . ' *** ' . $this->event->nick . ' ' . $this->event->get_arg('action'));
		$this->failnet->log->log($this->event->get_arg('action'), $this->event->nick, $this->event->get_arg('target'), true);
	}
	
	public function post_dispatch(array $events)
	{
		foreach($events as $event)
		{
			// First we figure out what we are going to say...
			$display = false;
			switch($event->type)
			{
				case 'join':
					// Already handled elsewhere.
				break;

				case 'part':
					// Already handled elsewhere.
				break;

				case 'kick':
					$display = 'add';
					$message = date('h:i') . ' ' . $this->failnet->get('nick') . ' has kicked user ' . $event->get_arg('user') . ' from ' . $event->get_arg('channel') . (($event->get_arg('comment')) ? ' : ' . '[' . $event->get_arg('comment') . ']' : '');
					$log = date('D m/d/Y - h:i:s A') . ' - === ' . $this->failnet->get('nick') . ' has kicked user ' . $event->get_arg('user') . ' from ' . $event->get_arg('channel') . (($event->get_arg('comment')) ? ' : ' . '[' . $event->get_arg('comment') . ']' : '');
				break;

				case 'invite':
					$display = 'add';
					$message = date('h:i') . ' ' . $this->failnet->get('nick') . ' has extended an invitation to ' . $event->get_arg('user') . ' for ' . $event->get_arg('channel');
				break;

				case 'quit':
					$display = 'add';
					$message = date('h:i') . ' ' . $this->failnet->get('nick') . ' has quit' . (($event->get_arg('message')) ? ' : ' . $event->get_arg('message') : '');
					$log = date('D m/d/Y - h:i:s A') . ' - === ' . $this->failnet->get('nick') . ' has quit' . (($event->get_arg('message')) ? ' : ' . $event->get_arg('message') : '');
				break;

				case 'topic':
					$display = 'add';
					$message = date('h:i') . ' ' . $this->failnet->get('nick') . ' has changed the topic in ' . $event->get_arg('channel') . ' to ' . $event->get_arg('topic');
					$log = date('D m/d/Y - h:i:s A') . ' - === ' . $this->failnet->get('nick') . ' has changed the topic in ' . $event->get_arg('channel') . ' to ' . $event->get_arg('topic');
				break;

				case 'mode':
					$display = 'add';
					$message = date('h:i') . ' ' . $this->failnet->get('nick') . ' has set mode ' . $event->get_arg('mode') . ' in ' . $event->get_arg('target') . ' on ' . $event->get_arg('user');
					$log = date('D m/d/Y - h:i:s A') . ' - === ' . $this->failnet->get('nick') . ' has set mode ' . $event->get_arg('mode') . ' ' . $event->get_arg('target') . ' ' . $event->get_arg('limit') . ' ' . $event->get_arg('user') . ' ' . $event->get_arg('banmask');
				break;

				case 'notice':
					$display = 'add';
					$message = date('h:i') . ' [Notice] ' . $this->failnet->get('nick') . ': ' . $this->event->get_arg('text');
					$log = date('D m/d/Y - h:i:s A') . ' - === Notice from ' . $this->failnet->get('nick') . ' : ' . $this->event->get_arg('text');
				break;

				case 'privmsg':
					$display = 'log';
					$message = date('h:i') . ' <' . $this->failnet->get('nick') . (($event->fromchannel()) ? '/' . $event->get_arg('receiver') : '') . '> ' . $event->get_arg('text');
					$log = $event->get_arg('text');
					$nick = $this->failnet->get('nick');
					$dest = $event->get_arg('receiver');
				break;

				case 'action':
					$display = 'log';
					$message = date('h:i') . (($event->fromchannel()) ? '[' . $event->get_arg('target') . ']' : '') . ' *** ' . $this->failnet->get('nick') . ' ' . $event->get_arg('action');
					$log = $event->get_arg('action');
					$nick = $this->failnet->get('nick');
					$dest = $event->get_arg('target');
				break;
			}

			// ...Then we display stuffs and log it all.
			if($display !== false)
			{
				display($message);
				if($display = 'log')
				{
					$this->failnet->log->log($log, $nick, $dest);
				}
				elseif($display = 'add')
				{
					$this->failnet->log->add($log);
				}
			}
		}
	}
}

?>