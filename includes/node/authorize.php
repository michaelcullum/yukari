<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * @version:	2.1.0 DEV
 * @copyright:	(c) 2009 - 2010 -- Failnet Project
 * @license:	http://opensource.org/licenses/gpl-2.0.php GNU GPL v2
 *
 *===================================================================
 *
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
 *
 */



/**
 * Failnet - User authorization handling class,
 * 		Used as Failnet's authorization handler.
 *
 *
 * @package nodes
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_node_authorize extends failnet_common
{

/**
 * Class properties
 */

	/**
	 * @var array - Access list cache property
	 */
	public $access = array();

	/**
	 * @var array - List of authlevels and their text translations
	 */
	public $authlevels = array();

/**
 * Class methods
 */

	/**
	 * Specialized init function to allow class construction to be easier.
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	public function init()
	{
		$this->authlevels = array(
			self::AUTH_UNKNOWNUSER		=> 'UNKNOWNUSER',
			self::AUTH_REGISTEREDUSER	=> 'REGISTEREDUSER',
			self::AUTH_KNOWNUSER		=> 'KNOWNUSER',
			self::AUTH_TRUSTEDUSER		=> 'TRUSTEDUSER',
			self::AUTH_ADMIN			=> 'ADMIN',
			self::AUTH_SUPERADMIN		=> 'SUPERADMIN',
			self::AUTH_OWNER			=> 'OWNER',
		);
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
		if($this->failnet->hash->check($password, $result['password']))
		{
			// Success!  We need to just add a row to the sessions table now so that the login persists.
			return $this->failnet->sql('sessions', 'create')->execute(array(':key' => $this->failnet->unique_id(), ':user' => $result['user_id'], ':time' => time(), ':hostmask' => $hostmask));
		}

		// FAIL!  NOW GIT OUT OF MAH KITCHEN!
		return false;
	}

	/**
	 * Looks up the authorization level for a certain user...
	 * @param string $hostmask - The hostmask for the user we're checking, if we want to use access lists for this.
	 * @return mixed - Always returns 100 if boolean false is used as the authlevel, integer for the authlevel if in the access list or logged in, or false if the user isn't logged in/session timed out/no such user.
	 */
	public function authlevel($hostmask)
	{
		// Just a quick hack for allowing us to use some functions internally.  ;)
		if($hostmask === false)
			return self::AUTH_OWNER;

		// First, we want to parse the user's hostmask here.
		parse_hostmask($hostmask, $nick, $user, $host);

		// Do some SQL to get our user ID for the user
		$this->failnet->sql('users', 'get')->execute(array(':nick' => $nick));
		$result = $this->failnet->sql('users', 'get')->fetch(PDO::FETCH_ASSOC);

		// Check to see if we have any results.
		if(!$result)
			return false;

		// Is this hostmask on the access list for this user?
		if($this->access((int) $result['user_id'], $hostmask))
		{
			return (int) $result['authlevel'];
		}
		else
		{
			// Okay, they aren't on the access list for that user.
			// What we'll have to do instead is to check to see if they are logged in.
			$sql = $this->failnet->db->query('SELECT u.authlevel, s.login_time
			FROM sessions s, users u
			WHERE u.user_id = s.user_id
				AND LOWER(u.nick) = LOWER(' . $this->failnet->db->quote($nick) . ')
			ORDER BY s.login_time DESC');

			$result = $sql->fetch(PDO::FETCH_ASSOC);

			if(!$result)
				return false;

			if(time() - $result['login_time'] < 3600)
				return false;

			return ($result) ? (int) $result['authlevel'] : false;
		}
	}

	/**
	 * Returns the authlevel for a specified user in the database.
	 * @param string $nick - The user's nickname to check
	 * @return mixed - Integer with authlevel if user found, if no such user boolean false.
	 */
	public function userlevel($nick)
	{
		if($nick === false)
			return self::AUTH_OWNER;

		$this->failnet->sql('users', 'get')->execute(array(':nick' => $nick));
		$result = $this->failnet->sql('users', 'get')->fetch(PDO::FETCH_ASSOC);

		return ($result) ? (int) $result['authlevel'] : false;
	}

	/**
	 * User management methods
	 */

	/**
	 * Add a user to the users database
	 * @param string $nick - Who should we set this for?
	 * @param string $password - The new password to use (will be stored as a hash)
	 * @param integer $authlevel - The authorization level to give to the user
	 * @return boolean - False if user already exists, true if successful.
	 */
	public function add_user($nick, $password, $authlevel)
	{
		$user_exists = $this->failnet->db->query('SELECT COUNT(*) FROM users WHERE nick = ' . $this->failnet->db->quote($nick))->fetchColumn();
		return (!$user_exists) ? $this->failnet->sql('users', 'create')->execute(array(':nick' => $nick, ':authlevel' => $authlevel, ':hash' => $this->failnet->hash->hash($password))) : false;
	}

	/**
	 * Deletes a user, but only after getting the user to confirm the deletion first.
	 * @param $hostmask - The hostmask of the user that is requesting they be deleted
	 * @param $password - The password for the user requesting they be deleted
	 * @return mixed - False if bad password, string containing confirm key if password is correct, and NULL if no such user.
	 */
	public function del_user($hostmask, $password)
	{
		// First, we want to parse the user's hostmask here.
		parse_hostmask($hostmask, $nick, $user, $host);

		// Now, let's do a query to grab the row for that user
		$this->failnet->sql('users', 'get')->execute(array(':nick' => $nick));
		$result = $this->failnet->sql('users', 'get')->fetch(PDO::FETCH_ASSOC);

		// No such user?  Derr...
		if(!$result)
			return NULL;

		// We should compare to see if this is the correct password that the user is sending to delete their user entry.
		if($this->failnet->hash->check($password, $result['password']))
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

		if($result['confirm_key'] == trim($confirm_key))
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
	 * Change the password for a user
	 * @param string $hostmask - The user requesting their password be changed
	 * @param string $old_pass - The old password for the user, to confirm the change
	 * @param string $new_pass - The new password for the user, will be set as the users password if the old password is correct
	 * @return mixed - NULL if no such user, false if incorrect password, true if password change was successful.
	 */
	public function set_pass($hostmask, $old_pass, $new_pass)
	{
		// First, we want to parse the user's hostmask here.
		parse_hostmask($hostmask, $nick, $user, $host);

		// Now, let's do a query to grab the row for that user
		$this->failnet->sql('users', 'get')->execute(array(':nick' => $nick));
		$result = $this->failnet->sql('users', 'get')->fetch(PDO::FETCH_ASSOC);

		// No such user?  Derr...
		if(!$result)
			return NULL;

		// We should compare to see if this is the correct password that the user is sending to change their password to something else.
		if($this->failnet->hash->check($old_pass, $result['password']))
		{
			$this->failnet->sql('users', 'set_pass')->execute(array(':hash' => $this->failnet->hash->hash($new_pass), ':user' => $result['user_id']));
			return true;
		}

		// FAIL!  NOW GIT OUT OF MAH KITCHEN!
		return false;
	}

	/**
	 * Set a user's authorization level
	 * @param string $nick - The username of the user to set the authorization level for
	 * @param integer $level - The authlevel to give the user
	 * @return mixed - NULL if no such user, true if set successfully
	 */
	public function set_authlevel($nick, $level)
	{
		// Now, let's do a query to grab the row for that user
		$this->failnet->sql('users', 'get')->execute(array(':nick' => $nick));
		$result = $this->failnet->sql('users', 'get')->fetch(PDO::FETCH_ASSOC);

		// No such user?  Derr...
		if(!$result)
			return NULL;

		if((int) $result['authlevel'] === self::AUTH_OWNER)
		{
			return false;
		}

		$this->failnet->sql('users', 'set_level')->execute(array(':user' => $result['user_id'], ':authlevel' => (int) $level));
		return true;
	}

	/**
	 * Access list methods
	 */

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
		return preg_match($this->access[(int) $user_id], $hostmask);
	}

	/**
	 * Adds a hostmask to the access list for a user
	 * @param string $hostmask - The hostmask to add, with the nick of the hostmask being the user to add to.
	 * @param string $password - User's password to confirm the change being made.
	 * @return mixed - Boolean true on success, false on invalid password, NULL on no such user.
	 */
	public function add_access($hostmask, $password, $mask = NULL)
	{
		// First, we want to parse the user's hostmask here.
		parse_hostmask($hostmask, $nick, $user, $host);

		// Now, let's check to see if the users hostmask is the target hostmask.
		if($mask === NULL)
			$mask = $hostmask;

		// Now, let's do a query to grab the row for that user
		$this->failnet->sql('users', 'get')->execute(array(':nick' => $nick));
		$result = $this->failnet->sql('users', 'get')->fetch(PDO::FETCH_ASSOC);

		// No such user?  Derr...
		if(!$result)
			return NULL;

		// Let's check that password now...
		if($this->failnet->hash->check($password, $result['password']))
		{
			// Success!  We need to just add a row to the sessions table now so that the login persists.
			$this->failnet->sql('access', 'create')->execute(array(':user' => $result['user_id'], ':hostmask' => $mask));

			// Clear out the hostmask cache
			if(isset($this->access[$result['user_id']]))
				unset($this->access[$result['user_id']]);
			return true;
		}

		// FAIL!  NOW GIT OUT OF MAH KITCHEN!
		return false;
	}

	/**
	 * Deletes a hostmask from a user's access list.
	 * @param string $hostmask - The hostmask to add, with the nick of the hostmask being the user to delete the hostmask access from.
	 * @param string $password - User's password to confirm the change being made.
	 * @return mixed - Boolean true on success, false on invalid password, NULL on no such user.
	 */
	public function delete_access($hostmask, $password, $mask = NULL)
	{
		// First, we want to parse the user's hostmask here.
		parse_hostmask($hostmask, $nick, $user, $host);

		// Now, let's check to see if the users hostmask is the target hostmask.
		if($mask === NULL)
			$mask = $hostmask;

		// Now, let's do a query to grab the row for that user
		$this->failnet->sql('users', 'get')->execute(array(':nick' => $nick));
		$result = $this->failnet->sql('users', 'get')->fetch(PDO::FETCH_ASSOC);

		// No such user?  Derr...
		if(!$result)
			return NULL;

		// Let's check that password now...
		if($this->failnet->hash->check($password, $result['password']))
		{
			// Success!  Now we just have to kill off that entry.
			$this->failnet->sql('access', 'delete')->execute(array(':user' => $result['user_id'], ':hostmask' => $mask));

			// Clear out the hostmask cache
			if(isset($this->access[$result['user_id']]))
				unset($this->access[$result['user_id']]);
			return true;
		}

		// FAIL!  NOW GIT OUT OF MAH KITCHEN!
		return false;
	}

	/**
	 * Translator method that translates between ye'olde integer-constants and what they represent.
	 * @param mixed $authlevel - The string name or the integer value of the authlevel to get the integer value/string name of.
	 * @return mixed - The desired data.
	 */
	public function identify_authlevel($authlevel)
	{
		if(is_int($authlevel))
		{
			return (isset($this->authlevels[$authlevel]) ? $this->authlevels[$authlevel] : NULL);
		}
		else
		{
			$authlevels = array_flip($this->authlevels);
			return (isset($authlevels[$authlevel]) ? $authlevels[$authlevel] : NULL);
		}
	}
}
