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

// @todo PDO and SQLite integration

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
 * Failnet - Ignore handling plugin,
 * 		Used as Failnet's user ignore system. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_plugin_ignore extends failnet_plugin_common
{
	public function cmd_connect()
	{
		$this->load();
	}
	
	public function cmd_privmsg()
	{
		$text = $this->event->get_arg('text');
		if(substr($text, 0, 1) != '|')
			return;

		$text = substr($text, 1);
		$sender = $this->event->nick;
		$cmd = (strpos($text, ' ') !== false) ? substr($text, 0, strpos($text, ' ')) : $text;
		switch ($cmd)
		{
			case 'ignores':
				$this->call_privmsg('Ignored users: ' . implode(', ', $this->failnet->ignore));
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
		}
	}
	
	// Gets the ignore file (re)loads the ignore list.
	public function load($sender = false)
	{
		if ($sender && $this->failnet->auth->authlevel($sender) > 9)
		{
			display('=== Loading ignored users/hostmasks list');
			$this->failnet->ignore = explode(PHP_EOL, file_get_contents('data/ignorelist')); 
			if($sender)
				$this->call_privmsg('Reloaded ignore list.');
		}
		elseif($sender)
		{
				$this->call_privmsg($this->failnet->deny());
		}
	}
	
	// Ignore a hostmask.
	public function ignore($sender, $victim)
	{
		if ($this->failnet->auth->authlevel($sender) > 9)
		{
			if($victim == $this->failnet->get('owner'))
			{
				$this->call_privmsg($this->failnet->deny());
				return;
			}
			if(!in_array($victim, $this->failnet->ignore))
			{
				$this->failnet->ignore[] = $victim; 
				file_put_contents('data/ignorelist', implode(PHP_EOL, $this->failnet->ignore));
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
	public function unignore($sender, $victim)
	{
		if ($this->failnet->auth->authlevel($sender) > 9)
		{
			foreach($this->failnet->ignore as $id => &$user)
			{
				if($user == $victim) unset($this->failnet->ignore[$id]);
			}
			file_put_contents('data/ignorelist', implode(PHP_EOL, $this->failnet->ignore));
			$this->call_privmsg('Hostmask "' . $victim . '" is no longer ignored.');
		}
		else
		{
			$this->call_privmsg($this->failnet->deny());
		}
	}
}

?>