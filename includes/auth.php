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

// @todo Rewrite with shell plugin!
// @todo Delete hostmask from access list
// @todo View access list for a user
// @todo Change password
// @todo Change authlevel for a user

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
	/**
	 * phpass hashing handler
	 * @var object
	 */
	public $hash;
	
	/**
	 * Access list cache property
	 * @var array
	 */
	public $access = array();

	/**
	 * Specialized init function to allow class construction to be easier.
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	public function init()
	{
		display('=== Loading Failnet password hashing system');
			$this->hash = new failnet_hash(8, false);
	}

	/**
	 * Attempt to authenticate a user..
	 * @param string $sender - The sender's nick.
	 * @param string $password - The password the sender specified.
	 * @return mixed - True if password is correct, false if password is wrong, NULL if no such user.
	 */
	public function auth($hostmask, $password)
	{
		// First, we want to parse the user's hostmask here.
		parse_hostmask($hostmask, $nick, $user, $host);
		
		// Now, let's do a query to grab the row for that user
		$this->failnet->sql('users', 'get')->execute(array(':nick' => $nick));
		$result = $this->failnet->sql('users', 'get')->fetch(PDO::FETCH_ASSOC); 
		
		// No such user?  Derr...
		if(!$result)
			return NULL;
		
		// Let's check that password now...
		if($this->hash->check($password, $result['hash']))
		{
			// Success!  We need to just add a row to the sessions table now so that the login persists.
			return $this->failnet->sql('sessions', 'create')->execute(array(':key' => $this->failnet->unique_id(), ':user' => $result['user_id'], ':time' => time(), ':hostmask' => $hostmask));
		}
		
		// FAIL!  NOW GIT OUT OF MAH KITCHEN!
		return false;
	}

	/**
	 * Looks up the authorization level for a certain user...
	 * @param string $nick - The user to check for.
	 * @param string $hostmask - The hostmask for the user we're checking, if we want to use access lists for this.
	 * @return mixed - Always returns 100 if boolean false is used as the authlevel, integer for the authlevel if in the access list or logged in, or false if the user isn't logged in/does not exist.
	 */
	public function authlevel($nick, $hostmask = false)
	{
		if($nick === false)
			return 100;

		if(!empty($hostmask))	
			parse_hostmask($hostmask, $nick, $user, $host);

		if(empty($hostmask))
		{
			$sql = $this->failnet->db->query('SELECT u.authlevel, s.login_time
				FROM sessions s, users u 
				WHERE u.user_id = s.user_id
					AND LOWER(u.nick) = LOWER(' . $this->failnet->db->quote($nick) . ') 
				ORDER BY s.login_time DESC');
			
			// Do we have a logged in user with that nick?
			$result = $sql->fetch(PDO::FETCH_ASSOC);

			return ($result) ? $result['authlevel'] : false;
		}
		else
		{
			$sql = $this->failnet->db->query('SELECT u.authlevel, u.user_id
				FROM access a, users u 
				WHERE (u.user_id = a.user_id AND LOWER(u.nick) = LOWER(' . $this->failnet->db->quote($nick) . ')');

			// Do we have a user with that nick in the DB?
			$result = $sql->fetch(PDO::FETCH_ASSOC);

			return ($result && $this->access($result['user_id'], $hostmask) ) ? $result['authlevel'] : false;
		}
	}

	/**
	 * Checks to see if a provided hostmask is currently in a user's access list.
	 * @param integer $user_id - The ID of the user that we are checking for hostmask access
	 * @param string $hostmask - Hostmask of the user that we are checking for hostmask access
	 * @return boolean - Is the hostmask in the access list?
	 */
	public function access($user_id, $hostmask)
	{
		// Check to see if we've got this user's access list cached.
		if(!isset($this->access[$user_id]))
		{
			// Guess not, so we run a query to see if we have a user with this ID in the access lists.  
			$this->failnet->sql('access', 'get')->execute(array(':user' => $user_id));
			$result = $this->failnet->sql('access', 'get')->fetchAll(PDO::FETCH_COLUMN, 0);
			$this->access[$user_id] = hostmasks_to_regex($result);
		}

		// Now that all that junk is taken care of, we need to actually check if this hostmask is in the access list.
		return preg_match($this->access[$user_id], $hostmask);
	}

	/**
	 * Add a user to the users database
	 * @param string $nick - Who should we set this for?
	 * @param string $password - The new password to use (will be stored as a hash)
	 * @param integer $authlevel - The authorization level to give to the user
	 * @return boolean - False if user already exists, true if successful.
	 */
	public function add_user($nick, $password, $authlevel = 0)
	{
		$user_exists = $this->failnet->db->query('SELECT COUNT(*) FROM users WHERE nick = ' . $this->failnet->db->quote($nick))->fetchColumn();
		return (!$user_exists) ? $this->failnet->sql('users', 'create')->execute(array(':nick' => $nick, ':authlevel' => $authlevel, ':hash' => $this->hash->hash($password))) : false;
	}
	
	/**
	 * Deletes a user, but only after getting the user to confirm the deletion first.
	 * @param $hostmask - The hostmask of the user that is requesting they be deleted
	 * @param $password - The password for the user requesting they be deleted
	 * @return mixed - False if invalid confirm key and bad password, string containing confirm key if password is correct, and NULL if no such user.  
	 */
	public function del_user($hostmask, $password)
	{ // @todo Possibly split this into two methods?
		// First, we want to parse the user's hostmask here.
		parse_hostmask($hostmask, $nick, $user, $host);
		
		// Now, let's do a query to grab the row for that user
		$this->failnet->sql('users', 'get')->execute(array(':nick' => $nick));
		$result = $this->failnet->sql('users', 'get')->fetch(PDO::FETCH_ASSOC); 
		
		// No such user?  Derr...
		if(!$result)
			return NULL;
		
		// We should compare to see if this is the confirmation key that the user is sending
		// ...if so, delete.  If not, check the password.
		
		if($this->hash->check($password, $result['hash']))
		{
			// Let's generate a unique ID for the confirm key.
			$confirm = $this->failnet->unique_id();
			$this->failnet->sql('users', 'set_confirm')->execute(array(':key' => $confirm, ':user' => $result['user_id']));
			return $confirm;
		}
		
		// FAIL!  NOW GIT OUT OF MAH KITCHEN!
		return false; 
	}
	
	/**
	 * Confirm deletion of a user and actually delete them.  :O
	 * @param string $hostmask - The hostmask requesting user deletion
	 * @param string $confirm_key - The confirmation key that...confirms the deletion.
	 * @return mixed - NULL if no such user, true if deletion successful, false if invalid confirmation ID.
	 */
	public function confirm_del($hostmask, $confirm_key)
	{
	// First, we want to parse the user's hostmask here.
		parse_hostmask($hostmask, $nick, $user, $host);
		
		// Now, let's do a query to grab the row for that user
		$this->failnet->sql('users', 'get')->execute(array(':nick' => $nick));
		$result = $this->failnet->sql('users', 'get')->fetch(PDO::FETCH_ASSOC); 
		
		// No such user?  Derr...
		if(!$result)
			return NULL;
		
		// We should compare to see if this is the confirmation key that the user is sending
		// ...if so, delete.  If not, check the password.
		
		if($result['confirm_key'] == trim($password))
		{
			$this->failnet->sql('access', 'delete_user')->execute(array(':user' => $result['user_id']));
			$this->failnet->sql('sessions', 'delete_user')->execute(array(':user' => $result['user_id']));
			$this->failnet->sql('users', 'delete')->execute(array(':user' => $result['user_id']));
			return true;
		}
		
		// FAIL!  NOW GIT OUT OF MAH KITCHEN!
		return false;
	}
	
	/**
	 * Adds a hostmask to the access list for a user
	 * @param string $hostmask - 
	 * @param string $password - 
	 * @return mixed - Boolean true on success, false on invalid password, NULL on no such user. 
	 */
	public function add_access($hostmask, $password)
	{
		// First, we want to parse the user's hostmask here.
		parse_hostmask($hostmask, $nick, $user, $host);
		
		// Now, let's do a query to grab the row for that user
		$this->failnet->sql('users', 'get')->execute(array(':nick' => $nick));
		$result = $this->failnet->sql('users', 'get')->fetch(PDO::FETCH_ASSOC); 
		
		// No such user?  Derr...
		if(!$result)
			return NULL;
		
		// Let's check that password now...
		if($this->hash->check($password, $result['hash']))
		{
			// Success!  We need to just add a row to the sessions table now so that the login persists.
			return $this->failnet->sql('access', 'create')->execute(array(':user' => $result['user_id'], ':hostmask' => $hostmask));
		}
		
		// FAIL!  NOW GIT OUT OF MAH KITCHEN!
		return false;
	}
}

?>