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
 * @todo: Rewrite with plugin!
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
	public $hmask_find = array('\\',   '^',   '$',   '.',   '[',   ']',   '|',   '(',   ')',   '?',   '+',   '{',   '}');
	public $hmask_repl = array('\\\\', '\\^', '\\$', '\\.', '\\[', '\\]', '\\|', '\\(', '\\)', '\\?', '\\+', '\\{', '\\}');
	
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
		$this->users = file(FAILNET_ROOT . 'data/users');
		foreach ($this->users as &$user)
		{
			$user = explode('::', rtrim($user));
		}
	}
	
	/**
	 * Instant auth.
	 * @param $user - Username to instantly authorize.
	 * @return boolean - Was it successful?
	 */
	public function instauth($user)
	{
		foreach ($this->users as &$user_row)
		{
			if ($user_row[0] == $user)
			{
				$user_row[3] = 1;
				file_put_contents('data/instantauth', 'nope');
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Attempt to authenticate a user..
	 * @param $sender - The sender's nick.
	 * @param $password - The password the sender specified.
	 * @return mixed - True if password is correct, false if password is wrong, NULL if no such user.
	 */
	public function auth($sender, $password)
	{
		foreach ($this->users as &$user)
		{
			if ($user[0] == strtolower($sender))
			{
				if ($this->hash->check($pw, $user[2]))
				{
					$user[3] = true;
					
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
	 * @param $person - The user to check for.
	 * @return mixed - Always returns 100 if boolean false is used as the authlevel, or if no such user NULL is returned.
	 */
	public function authlevel($person)
	{
		if($person === false)
			return 100;
		foreach ($this->users as &$user)
		{
			if ($user[0] == $person) return (!empty($user[3])) ? $user[1] : false;
		}
		return NULL;
	}
	
	/**
	 * Add a user to the users database
	 * @param $nick - Who should we set this for?
	 * @param $password - The new password to use
	 * @return boolean - False if user already exists, true if successful.
	 */
	public function adduser($nick, $password)
	{
		foreach ($this->users as &$user)
		{
			if ($user[0] == $nick) return false;
		}
		file_put_contents('data/users', PHP_EOL . $nick . '::0::' . $this->hash->hash($password), FILE_APPEND);
		return true;
	}
	
	/**
	 * Parses a IRC hostmask and sets nick, user and host bits.
	 *
	 * @param string $hostmask Hostmask to parse
	 * @param string $nick Container for the nick
	 * @param string $user Container for the username
	 * @param string $host Container for the hostname
	 * @return void
	 * 
	 * @author Phergie Development Team {@link http://code.assembla.com/phergie/subversion/nodes}
	 */
	public function parse_hostmask($hostmask, &$nick, &$user, &$host)
	{
		if (preg_match('/^([^!@]+)!([^@]+)@(.*)$/', $hostmask, $match) > 0)
		{
			list(, $nick, $user, $host) = array_pad($match, 4, NULL);
		}
		else
		{
			$host = $hostmask;
		}
	}

	/**
	 * Converts a delimited string of hostmasks into a regular expression
	 * that will match any hostmask in the original string.
	 *
	 * @param string $list Delimited string of hostmasks
	 * @return string Regular expression
	 * 
	 * @author Phergie Development Team {@link http://code.assembla.com/phergie/subversion/nodes}
	 */
	public function hostmasks_to_regex($list)
	{
		$patterns = array();

		foreach(preg_split('#[\s\r\n,]+#', $list) as $hostmask)
		{
			// Find out which chars are present in the config mask and exclude them from the regex match
			$excluded = '';
			if (strpos($hostmask, '!') !== false)
			{
				$excluded .= '!';
			}
			if (strpos($hostmask, '@') !== false)
			{
				$excluded .= '@';
			}

			// Escape regex meta characters
			$hostmask = str_replace($this->hmask_find, $this->hmask_repl, $hostmask);

			// Replace * so that they match correctly in a regex
			$patterns[] = str_replace('*', ($excluded === '' ? '.*' : '[^' . $excluded . ']*'), $hostmask);
		}

		return ('#^' . implode('|', $patterns) . '$#i');
	}
}

?>