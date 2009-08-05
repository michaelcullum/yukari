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
			// Add a new user to the DB
			case 'newuser':
			case 'adduser':
				$added = $this->failnet->auth->adduser($sender, $text);
				$this->call_notice($sender, ($added) ? 'You were successfully added to my users database.' : 'I\'m sorry, but I was unable to add you to my users database.');
			break;

			// Log the user in
			case 'login':
			case 'auth':
				$login = $this->failnet->auth->auth($this->event->gethostmask(), $text);
				if(is_null($login))
					$this->call_notice($sender, 'Cannot login -- no such user exists in database');

				$this->call_notice($sender, ($login) ? 'You have been logged in.' : 'Cannot login -- invalid password entered');
			break;

			// Delete user from the DB
			case 'deluser':
			case 'dropuser':
				$confirm = $this->failnet->auth->deluser($this->event->gethostmask(), $text);
				if(is_null($confirm))
					$this->call_notice($sender, 'Cannot remove user -- no such user exists in database');

				$this->call_notice($sender, ($confirm) ? 'To completely remove yourself from my users database, please reply with |delconfirm ' . $confirm : 'Cannot remove user -- invalid password entered');
			break;

			// Confirm user deletion from DB
			case 'confirmdel':
			case 'delconfirm':
				$success = $this->failnet->auth->confirm_del($this->event->gethostmask(), $text);
				if(is_null($success))
					$this->call_notice($sender, 'Cannot remove user -- no such user exists in database');

				$this->call_notice($sender, ($success) ? 'You have been removed from my users database.' : 'Cannot remove user -- invalid confirmation key entered');	
			break;

			// Change the password for this user
			case 'pass':
			case 'setpass':
				$param = explode(' ', $text);
				$success = $this->failnet->auth->setpass($this->event->gethostmask(), $param[0], $param[1]);
				if(is_null($success))
					$this->call_notice($sender, 'Cannot change password for user -- no such user exists in database');

				$this->call_notice($sender, ($success) ? 'Your password has been successfully changed.' : 'Cannot change password for user -- invalid original password entered');
			break;

			// Add current hostmask to the access list
			case '+access':
				$success = $this->failnet->auth->add_access($this->event->gethostmask(), $text);
				if(is_null($success))
					$this->call_notice($senderk, 'Cannot add hostmask to access list for user -- no such user exists in database');

				$this->call_notice($sender, ($success) ? 'Hostmask successfully added to access list for user.' : 'Cannot add hostmask to access list for user -- invalid password entered');
			break;

			// Remove current hostmask from the access list
			case '-access':
				$success = $this->failnet->auth->delete_access($this->event->gethostmask(), $text);
				if(is_null($success))
					$this->call_notice($sender, 'Cannot remove hostmask from access list for user -- no such user exists in database');

				$this->call_notice($sender, ($success) ? 'Hostmask successfully removed access list for user.' : 'Cannot remove hostmask from access list for user -- invalid password entered');
			break;

			// Add a specific hostmask to the access list
			case 'newaccess':
			case 'addaccess':
				$param = explode(' ', $text);
				$success = $this->failnet->auth->add_access($this->event->gethostmask(), $param[1], $param[0]);
				if(is_null($success))
					$this->call_notice($senderk, 'Cannot add hostmask to access list for user -- no such user exists in database');

				$this->call_notice($sender, ($success) ? 'Hostmask successfully added to access list for user.' : 'Cannot add hostmask to access list for user -- invalid password entered');
			break;

			// Remove a specific hostmask from the access list
			case 'dropaccess':
			case 'delaccess':
				$param = explode(' ', $text);
				$success = $this->failnet->auth->delete_access($this->event->gethostmask(), $param[1], $param[0]);
				if(is_null($success))
					$this->call_notice($sender, 'Cannot remove hostmask from access list for user -- no such user exists in database');

				$this->call_notice($sender, ($success) ? 'Hostmask successfully removed access list for user.' : 'Cannot remove hostmask from access list for user -- invalid password entered');
			break;
		}
	}
}

?>