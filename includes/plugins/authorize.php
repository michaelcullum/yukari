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
 * Failnet - Authorization plugin,
 * 		Full authorization system integration plugin.  Handles login, new users, etc. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_plugin_authorize extends failnet_plugin_common
{
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
			case 'adduser':
				$added = $this->failnet->auth->adduser($sender, substr($text, strpos($text, ' ') + 1));
				$this->call_notice($this->event->nick, ($added) ? 'You were successfully added to my users database.' : 'I\'m sorry, but I was unable to add you to my users database.');
			break;

			case 'auth':
				$login = $this->failnet->auth->auth($this->event->gethostmask(), substr($text, strpos($text, ' ') + 1));
				if(is_null($login))
					$this->call_notice($this->event->nick, 'Cannot login -- no such user exists in database');

				$this->call_notice($this->event->nick, ($login) ? 'You have been logged in.' : 'Cannot login -- invalid password entered');
			break;

			case 'deluser':
				$confirm = $this->failnet->auth->deluser($this->event->gethostmask(), substr($text, strpos($text, ' ') + 1));
				if(is_null($confirm))
					$this->call_notice($this->event->nick, 'Cannot remove user -- no such user exists in database');

				$this->call_notice($this->event->nick, ($confirm) ? 'To completely remove yourself from my users database, please reply with |delconfirm ' . $confirm : 'Cannot remove user -- invalid password entered');
			break;

			case 'delconfirm':
				$success = $this->failnet->auth->confirm_del($this->event->gethostmask(), substr($text, strpos($text, ' ') + 1));
				if(is_null($confirm))
					$this->call_notice($this->event->nick, 'Cannot remove user -- no such user exists in database');

				$this->call_notice($this->event->nick, ($success) ? 'You have been removed from my users database.' : 'Cannot remove user -- invalid confirmation key entered');	
			break;
		}
	}
}

?>