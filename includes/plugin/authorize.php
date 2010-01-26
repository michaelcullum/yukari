<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 1
 * Copyright:	(c) 2009 - Failnet Project
 * License:		GNU General Public License - Version 2
 *
 *===================================================================
 *
 * @todo add a command for altering another user's authlevel
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
 * Failnet - Authorization plugin,
 * 		Full authorization system integration plugin.  Handles login, new users, etc.
 *
 *
 * @package plugins
 * @author Obsidian
 * @copyright (c) 2009 - Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_plugin_authorize extends failnet_plugin_common
{
	public function help(&$name, &$commands)
	{
		$name = 'authorize';
		$commands = array(
			'newuser'		=> 'newuser {$password} - (no auth) - Adds the sender to Failnet`s list of known users',
			'login'			=> 'login {$password} - (requires valid password) - Logs in the current user if the password matches that of the registered user`s password',
			'deluser'		=> 'deluser {$password} - (requires valid password) - Prepares to delete a specified user from Failnet`s list of known users',
			'setpass'		=> 'setpass {$old_password} {$new_password} - (requires valid password) - Changes the password for the current user new {$new_password}',
			'setauth'		=> 'setauth {$username} {$authlevel} - (authlevel > $authlevel) - Sets the authlevel for the specified user if the requesting user is above that level.  Will not lower the authlevel of users with OWNER authlevel.',
			'authlevel'		=> 'authlevel {$username} - (no auth) - Fetches the specified user`s current authlevel.',
			'+access'		=> '+access {$password} - (requires valid password) - Adds the user`s current hostmask to the access list of the current user',
			'-access'		=> '-access {$password} - (requires valid password) - Removes the user`s current hostmask from the access list of the current user',
			'addaccess'		=> 'addaccess {$hostmask} {$password} - (requires valid password) - Adds the specified hostmask to the access list of the current user',
			'delaccess'		=> 'delaccess {$hostmask} {$password} - (requires valid password) - Removes the specified hostmaks from the access list of the current user',
		);
	}

	public function cmd_privmsg()
	{
		// Process the command
		$text = $this->event->get_arg('text');
		if(!$this->prefix($text))
			return;

		$cmd = $this->purify($text);
		$sender = ($this->failnet->get('speak')) ? $this->event->source() : $this->event->hostmask->nick;
		$hostmask = $this->event->hostmask;
		switch($cmd)
		{
			// Add a new user to the DB
			case 'newuser':
			case 'adduser':
				if(is_null($text))
				{
					$this->call_privmsg($sender, 'Invalid arguments specified for command');
					return;
				}

				$success = $this->failnet->authorize->add_user($sender, $text);
				$this->call_privmsg($sender, ($success) ? 'You were successfully added to my users database.' : 'I\'m sorry, but I was unable to add you to my users database.');
			break;

			// Log the user in
			case 'login':
			case 'auth':
				if(is_null($text))
				{
					$this->call_privmsg($sender, 'Invalid arguments specified for command');
					return;
				}

				$success = $this->failnet->authorize->auth($hostmask, $text);
				if(is_null($success))
				{
					$this->call_privmsg($sender, 'Cannot login -- no such user exists in database');
					return;
				}

				$this->call_privmsg($sender, ($success) ? 'You have been logged in.' : 'Cannot login -- invalid password entered');
			break;

			// Delete user from the DB
			case 'deluser':
			case 'dropuser':
				if(is_null($text))
				{
					$this->call_privmsg($sender, 'Invalid arguments specified for command');
					return;
				}

				$confirm = $this->failnet->authorize->del_user($hostmask, $text);
				if(is_null($confirm))
				{
					$this->call_privmsg($sender, 'Cannot remove user -- no such user exists in database');
					return;
				}

				$this->call_privmsg($sender, ($confirm) ? 'To completely remove yourself from my users database, please reply with |delconfirm ' . $confirm : 'Cannot remove user -- invalid password entered');
			break;

			// Confirm user deletion from DB
			case 'confirmdel':
			case 'delconfirm':
				if(is_null($text))
				{
					$this->call_privmsg($sender, 'Invalid arguments specified for command');
					return;
				}

				$success = $this->failnet->authorize->confirm_del($hostmask, $text);
				if(is_null($success))
				{
					$this->call_privmsg($sender, 'Cannot remove user -- no such user exists in database');
					return;
				}

				$this->call_privmsg($sender, ($success) ? 'You have been removed from my users database.' : 'Cannot remove user -- invalid confirmation key entered');
			break;

			// Change the password for this user
			case 'pass':
			case 'setpass':
				if(is_null($text))
				{
					$this->call_privmsg($sender, 'Invalid arguments specified for command');
					return;
				}

				$param = explode(' ', $text);
				$success = $this->failnet->authorize->set_pass($hostmask, $param[0], $param[1]);
				if(is_null($success))
				{
					$this->call_privmsg($sender, 'Cannot change password for user -- no such user exists in database');
					return;
				}

				$this->call_privmsg($sender, ($success) ? 'Your password has been successfully changed.' : 'Cannot change password for user -- invalid original password entered');
			break;

			case 'userlevel':
			case 'authlevel':
				if(is_null($text))
				{
					$this->call_privmsg($sender, 'Invalid arguments specified for command');
					return;
				}

				$userlevel = $this->failnet->authorize->userlevel($text);
				$this->call_privmsg($sender, 'User`s current authlevel is ' . $this->failnet->authorize->identify_authlevel(($userlevel) ? $userlevel : self::AUTH_UNKNOWNUSER) . '.');
			break;

			case 'setlevel':
			case 'setauth':
			case 'setauthlevel':
				if(is_null($text))
				{
					$this->call_privmsg($sender, 'Invalid arguments specified for command');
					return;
				}
				$param = explode(' ', $text);

				// Identify the level we want to use
				$level = $this->failnet->authorize->identify_authlevel($param[1]);
				if(is_null($level))
				{
					$this->call_privmsg($sender, 'Invalid new level specified for command');
					return;
				}

				// Check auths
				if ($this->failnet->authorize->authlevel($hostmask) < $level)
				{
					$this->call_privmsg($sender, $this->failnet->deny());
					return;
				}

				$success = $this->failnet->authorize->set_authlevel($param[0], $level);
				if(is_null($success))
				{
					$this->call_privmsg($sender, 'Invalid user specified for command');
				}

				$this->call_privmsg($sender, 'New authlevel successfully set for specified user');
			break;

			// Add current hostmask to the access list
			case '+access':
				if(is_null($text))
				{
					$this->call_privmsg($sender, 'Invalid arguments specified for command');
					return;
				}

				$success = $this->failnet->authorize->add_access($hostmask, $text);
				if(is_null($success))
				{
					$this->call_privmsg($sender, 'Cannot add hostmask to access list for user -- no such user exists in database');
					return;
				}

				$this->call_privmsg($sender, ($success) ? 'Hostmask successfully added to access list for user.' : 'Cannot add hostmask to access list for user -- invalid password entered');
			break;

			// Remove current hostmask from the access list
			case '-access':
				if(is_null($text))
				{
					$this->call_privmsg($sender, 'Invalid arguments specified for command');
					return;
				}

				$success = $this->failnet->authorize->delete_access($hostmask, $text);
				if(is_null($success))
				{
					$this->call_privmsg($sender, 'Cannot remove hostmask from access list for user -- no such user exists in database');
					return;
				}

				$this->call_privmsg($sender, ($success) ? 'Hostmask successfully removed access list for user.' : 'Cannot remove hostmask from access list for user -- invalid password entered');
			break;

			// Add a specific hostmask to the access list
			case 'newaccess':
			case 'addaccess':
				if(is_null($text))
				{
					$this->call_privmsg($sender, 'Invalid arguments specified for command');
					return;
				}
				$param = explode(' ', $text);
				$success = $this->failnet->authorize->add_access($hostmask, $param[1], $param[0]);
				if(is_null($success))
				{
					$this->call_privmsg($senderk, 'Cannot add hostmask to access list for user -- no such user exists in database');
					return;
				}

				$this->call_privmsg($sender, ($success) ? 'Hostmask successfully added to access list for user.' : 'Cannot add hostmask to access list for user -- invalid password entered');
			break;

			// Remove a specific hostmask from the access list
			case 'dropaccess':
			case 'delaccess':
				if(is_null($text))
				{
					$this->call_privmsg($sender, 'Invalid arguments specified for command');
					return;
				}

				$param = explode(' ', $text);
				$success = $this->failnet->authorize->delete_access($hostmask, $param[1], $param[0]);
				if(is_null($success))
				{
					$this->call_privmsg($sender, 'Cannot remove hostmask from access list for user -- no such user exists in database');
					return;
				}

				$this->call_privmsg($sender, ($success) ? 'Hostmask successfully removed access list for user.' : 'Cannot remove hostmask from access list for user -- invalid password entered');
			break;
		}
	}
}

?>