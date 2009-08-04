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
		if(!$this->prefix($text))
			return;

		$cmd = $this->purify($text);
		$sender = $this->event->nick;
		switch($cmd)
		{
			case 'newuser':
			case 'adduser':
				$added = $this->failnet->auth->adduser($sender, substr($text, strpos($text, ' ') + 1));
				$this->call_notice($this->event->nick, ($added) ? 'You were successfully added to my users database.' : 'I\'m sorry, but I was unable to add you to my users database.');
			break;

			case 'login':
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

			case 'confirmdel':
			case 'delconfirm':
				$success = $this->failnet->auth->confirm_del($this->event->gethostmask(), substr($text, strpos($text, ' ') + 1));
				if(is_null($success))
					$this->call_notice($this->event->nick, 'Cannot remove user -- no such user exists in database');

				$this->call_notice($this->event->nick, ($success) ? 'You have been removed from my users database.' : 'Cannot remove user -- invalid confirmation key entered');	
			break;

			case 'pass':
			case 'setpass':
				$pass = explode(' ', substr($text, strpos($text, ' ') + 1));
				$success = $this->failnet->auth->setpass($this->event->gethostmask(), $pass[0], $pass[1]);
				if(is_null($success))
					$this->call_notice($this->event->nick, 'Cannot change password for user -- no such user exists in database');

				$this->call_notice($this->event->nick, ($success) ? 'Your password has been successfully changed.' : 'Cannot change password for user -- invalid original password entered');
			break;

			case 'addaccess':
			case 'newaccess':
			case '+access':
				$success = $this->failnet->auth->add_access($this->event->gethostmask(), substr($text, strpos($text, ' ') + 1));
				if(is_null($success))
					$this->call_notice($this->event->nick, 'Cannot add hostmask to access list for user -- no such user exists in database');

				$this->call_notice($this->event->nick, ($success) ? 'Hostmask successfully added to access list for user.' : 'Cannot add hostmask to access list for user -- invalid password entered');
			break;

			case 'delaccess':
			case 'removeaccess':
			case 'dropaccess':
			case '-access':
				$success = $this->failnet->auth->delete_access($this->event->gethostmask(), substr($text, strpos($text, ' ') + 1));
				if(is_null($success))
					$this->call_notice($this->event->nick, 'Cannot remove hostmask from access list for user -- no such user exists in database');

				$this->call_notice($this->event->nick, ($success) ? 'Hostmask successfully removed access list for user.' : 'Cannot remove hostmask from access list for user -- invalid password entered');
			break;
		}
	}
}

?>