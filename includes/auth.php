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

// @todo Rewrite with plugin!

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
 * Failnet - User authorization handling class,
 * 		Used as Failnet's authorization handler. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_auth extends failnet_common
{
	// Authed users.
	public $users = array();
	
	/**
	 * phpass hashing handler
	 * @var object
	 */
	public $hash;
	
	/**
	 * Specialized init function to allow class construction to be easier.
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	public function init()
	{
		display('=== Loading Failnet password hashing system');
			$this->hash = new failnet_hash(8, false);
		display('=== Loading user database'); 
			$this->load();
	}
	
	/**
	 * Method to (re)load the users database.
	 * @return void
	 */
	public function load()
	{
		$this->users = file(FAILNET_ROOT . 'data/users', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($this->users as &$user)
		{
			$user_row = explode('::', rtrim($user));
			$user['nick'] = array_shift($user_row);
			$user['level'] = array_shift($user_row);
			$user['hash'] = array_shift($user_row);
			$user['authed'] = false;
			$user['hosts'] = (!empty($user_row)) ? $user_row : array();
			$user['regex'] = hostmasks_to_regex($user['hosts']);
		}
	}
	
	/**
	 * Attempt to authenticate a user..
	 * @param string $sender - The sender's nick.
	 * @param string $password - The password the sender specified.
	 * @return mixed - True if password is correct, false if password is wrong, NULL if no such user.
	 */
	public function auth($sender, $password)
	{
		foreach ($this->users as &$user)
		{
			if ($user['nick'] == strtolower($sender))
			{
				if ($this->hash->check($password, $user['hash']))
				{
					$user['authed'] = true;
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		return NULL;
	}

	/**
	 * Adds a hostmask to 
	 * @param $sender
	 * @param $hostmask
	 * @return mixed - Boolean true on success, false on invalid password, NULL on no such user. 
	 */
	public function add_access($sender, $hostmask, $password)
	{
		foreach ($this->users as &$user)
		{
			if ($user['nick'] == strtolower($sender))
			{
				// Check password first..
				if ($this->hash->check($password, $user['hash']))
				{
					$user['hosts'][] = $hostmask; 
					$user['regex'] = hostmasks_to_regex($user['hosts']);

					// We need to push the new hostmask entry to the users DB file now...
					$list = file(FAILNET_ROOT . 'data/users', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
					foreach($list as &$item)
					{
						$item = explode('::', rtrim($item));
						if($item[0] == $sender)
							$item[] = $hostmask;
						$item = implode('::', rtrim($item));
					}

					// New that we've got it current...we need to write it back to the users DB file.  
					// We want to OVERWRITE in this case, also.
					file_put_contents(FAILNET_ROOT . 'data/users', implode(PHP_EOL, $list));
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		return NULL;
	}
	
	/**
	 * Looks up the authorization level for a certain user...
	 * @param string $nick - The user to check for.
	 * @param string $hostmask - The hostmask for the user we're checking, if we want to use access lists for this.
	 * @return mixed - Always returns 100 if boolean false is used as the authlevel, or if no such user NULL is returned.
	 */
	public function authlevel($nick, $hostmask = false)
	{
		if($nick === false)
			return 100;

		if(!empty($hostmask))	
			parse_hostmask($hostmask, $nick, $user, $host);

		foreach ($this->users as &$user)
		{
			if ($user['nick'] == $nick)
			{
				if(!empty($hostmask) && preg_match($user['regex'], $hostmask))
				{
					return $user['level'];
				}
				else
				{
					return (!empty($user['authed'])) ? $user['level'] : false;
				}
			}
		}
		return NULL;
	}
	
	/**
	 * Add a user to the users database
	 * @param string $nick - Who should we set this for?
	 * @param string $password - The new password to use (will be stored as a hash)
	 * @param integer $authlevel -
	 * @return boolean - False if user already exists, true if successful.
	 */
	public function adduser($nick, $password, $authlevel = 0)
	{
		foreach ($this->users as &$user)
		{
			if ($user[0] == $nick)
				return false;
		}
		file_put_contents(FAILNET_ROOT . 'data/users', PHP_EOL . strtolower($nick) . '::' . (int) $authlevel . '::' . $this->hash->hash($password), FILE_APPEND);
		return true;
	}
}

?>