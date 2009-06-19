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
 * @TODO: Rewrite as a plugin.
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
if(!defined('IN_FAILNET')) return;


/**
 * Failnet - Ignore handling class,
 * 		Used as Failnet's user ignoring handler class. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_ignore extends failnet_common
{
	public $ignore = array();
	public $host_ignore = array();
	
	public function init()
	{
		display('=== Loading ignored users/hostmasks list');
			$this->load();
	}
	
	// Gets the ignore file (re)loads the ignore list.
	public function load($sender = false)
	{
		if ($sender && $this->failnet->auth->authlevel($sender) > 9)
		{
			$this->ignore = explode(', ', file_get_contents('data/ignore_users')); 
			$this->host_ignore = explode(', ', file_get_contents('data/ignore_hosts'));
			$this->failnet->irc->privmsg('Reloaded ignore list.');
		}
		else
		{
			$this->failnet->deny();
		}
	}
	
	// Ignore a user.
	public function ignore($sender, $victim)
	{
		if ($this->failnet->auth->authlevel($sender) > 9)
		{
			if($victim == $this->failnet->owner)
			{
				$this->failnet->deny();
				return;
			}
			if(!in_array($victim, $this->ignore))
			{
				$this->ignore[] = $victim; 
				file_put_contents('data/ignore_users', implode(', ', $this->ignore));
				$this->failnet->irc->privmsg('User "' . $victim . '" is now ignored.'); 
			}
			else
			{
					$this->failnet->irc->privmsg('User "' . $victim . '" is already ignored.');
			}
		}
		else
		{
			$this->failnet->deny();
		}
	}
	
	// 	// Unignore a user.
	public function unignore($sender, $victim)
	{
		if ($this->failnet->auth->authlevel($sender) > 9)
		{
			foreach($this->ignore as $id => &$user)
			{
				if($user == $victim) unset($this->ignore[$id]);
			}
			file_put_contents('data/ignore_users', implode(', ', $this->ignore));
			$this->failnet->irc->privmsg('User "' . $victim . '" is no longer ignored.');
		}
		else
		{
			$this->failnet->deny();
		}
	}
	
	// Ignore a hostmask.
	public function ignore_host($sender, $victim)
	{
		if ($this->failnet->auth->authlevel($sender) > 9)
		{
			if($victim == $this->failnet->owner)
			{
				$this->failnet->deny();
				return;
			}
			if(!in_array($victim, $this->ignore))
			{
				$this->host_ignore[] = $victim; 
				file_put_contents('data/ignore_hosts', implode(', ', $this->host_ignore));
				$this->failnet->irc->privmsg('Host "' . $victim . '" is now ignored.'); 
			}
			else
			{
					$this->failnet->irc->privmsg('Host "' . $victim . '" is already ignored.');
			}
		}
		else
		{
			$this->failnet->deny();
		}
	}
	
	// 	// Unignore a hostmask.
	public function unignore_host($sender, $victim)
	{
		if ($this->failnet->auth->authlevel($sender) > 9)
		{
			foreach($this->host_ignore as $id => &$user)
			{
				if($user == $victim) unset($this->host_ignore[$id]);
			}
			file_put_contents('data/ignore_hosts', implode(', ', $this->host_ignore));
			$this->failnet->irc->privmsg('User "' . $victim . '" is no longer ignored.');
		}
		else
		{
			$this->failnet->deny();
		}
	}
}

?>