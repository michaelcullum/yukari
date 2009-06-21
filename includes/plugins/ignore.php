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
if(!defined('IN_FAILNET')) return;


/**
 * Failnet - Ignore handling plugin,
 * 		Used as Failnet's user ignore system. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_plugin_ignore extends failnet_common
{
	public $ignore = array();
	public $host_ignore = array();
	
	
	
	public function cmd_connect()
	{
		$this->load();
	}
	
	public function cmd_privmsg()
	{
		$text = $this->event->get_arg('text');
		if(substr($text, 0, 1) != '|')
		{
			return;
		}
		else
		{
			$text = substr($text, 1);
		}
		$sender = $this->event->nick;
		$cmd = (strpos($text, ' ') !== false) ? substr($text, 0, strpos($text, ' ')) : $text;
		switch ($cmd)
		{
			case 'ignores':
				$this->call_privmsg('Ignored users: ' . implode(', ', $this->ignore));
			break;
			
			case 'hostignores':
				$this->call_privmsg('Ignored hostmasks: ' . implode(', ', $this->host_ignore));
			break;
			
			case 'loadignores':
			case 'loadignore':
			case 'reloadignores':
				$this->load($sender);
			break;
			
			case 'ignore':
			case '+ignore':
				$this->ignore($sender, substr($text, strpos($text, ' ')) + 1);
			break;
			
			case 'unignore':
			case '-ignore':
				$this->unignore($sender, substr($text, strpos($text, ' ')) + 1);
			break;
			
			case 'hignore':
			case 'hostignore':
			case 'ignorehost':
			case '+hignore':
				$this->ignore_host($sender, substr($text, strpos($text, ' ')) + 1);
			break;
			
			case 'unhignore':
			case 'hostunignore':
			case 'unignorehost':
			case '-hignore':
				$this->unignore_host($sender, substr($text, strpos($text, ' ')) + 1);
			break;
		}
	}
	
	// Gets the ignore file (re)loads the ignore list.
	public function load($sender = false)
	{
		if ($sender && $this->failnet->auth->authlevel($sender) > 9)
		{
			display('=== Loading ignored users/hostmasks list');
			$this->ignore = explode(PHP_EOL, file_get_contents('data/ignore_users')); 
			$this->host_ignore = explode(PHP_EOL, file_get_contents('data/ignore_hosts'));
			$this->call_privmsg('Reloaded ignore list.');
		}
		elseif(!$sender)
		{
			display('=== Loading ignored users/hostmasks list');
			$this->ignore = explode(PHP_EOL, file_get_contents('data/ignore_users')); 
			$this->host_ignore = explode(PHP_EOL, file_get_contents('data/ignore_hosts'));
		}
		else
		{
			$this->call_privmsg($this->failnet->deny());
		}
	}
	
	// Ignore a user.
	public function ignore($sender, $victim)
	{
		if ($this->failnet->auth->authlevel($sender) > 9)
		{
			if($victim == $this->failnet->owner)
			{
				$this->call_privmsg($this->failnet->deny());
				return;
			}
			if(!in_array($victim, $this->ignore))
			{
				$this->ignore[] = $victim; 
				file_put_contents('data/ignore_users', implode(PHP_EOL, $this->ignore));
				$this->call_privmsg('User "' . $victim . '" is now ignored.'); 
			}
			else
			{
				$this->call_privmsg('User "' . $victim . '" is already ignored.');
			}
		}
		else
		{
			$this->call_privmsg($this->failnet->deny());
		}
	}
	
	// Unignore a user.
	public function unignore($sender, $victim)
	{
		if ($this->failnet->auth->authlevel($sender) > 9)
		{
			foreach($this->ignore as $id => &$user)
			{
				if($user == $victim) unset($this->ignore[$id]);
			}
			file_put_contents('data/ignore_users', implode(PHP_EOL, $this->ignore));
			$this->call_privmsg('User "' . $victim . '" is no longer ignored.');
		}
		else
		{
			$this->call_privmsg($this->failnet->deny());
		}
	}
	
	// Ignore a hostmask.
	public function ignore_host($sender, $victim)
	{
		if ($this->failnet->auth->authlevel($sender) > 9)
		{
			if($victim == $this->failnet->owner)
			{
				$this->call_privmsg($this->failnet->deny());
				return;
			}
			if(!in_array($victim, $this->ignore))
			{
				$this->host_ignore[] = $victim; 
				file_put_contents('data/ignore_hosts', implode(PHP_EOL, $this->host_ignore));
				$this->call_privmsg('Hostmask "' . $victim . '" is now ignored.'); 
			}
			else
			{
				$this->call_privmsg('Hostmask "' . $victim . '" is already ignored.');
			}
		}
		else
		{
			$this->call_privmsg($this->failnet->deny());
		}
	}
	
	// Unignore a hostmask.
	public function unignore_host($sender, $victim)
	{
		if ($this->failnet->auth->authlevel($sender) > 9)
		{
			foreach($this->host_ignore as $id => &$user)
			{
				if($user == $victim) unset($this->host_ignore[$id]);
			}
			file_put_contents('data/ignore_hosts', implode(PHP_EOL, $this->host_ignore));
			$this->call_privmsg('Hostmask "' . $victim . '" is no longer ignored.');
		}
		else
		{
			$this->call_privmsg($this->failnet->deny());
		}
	}
}

?>