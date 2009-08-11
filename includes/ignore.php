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
 * Copyright:	(c) 2009 - Failnet Project
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
if(!defined('IN_FAILNET')) exit(1);


/**
 * Failnet - Error handling class,
 * 		Used as Failnet's error handler. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_ignore extends failnet_common
{
	/**
	 * preg_match pattern cache used to check for an ignored user 
	 * @var string
	 */
	private $cache = '';

	/**
	 * List of ignored user hostmasks, used to rebuild the preg_match ignore pattern when necessary 
	 * @var unknown_type
	 */
	private $users = array();
	
	/**
	 * Specialized init function to allow class construction to be easier.
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	public function init()
	{
		$table_exists = $this->failnet->db->query('SELECT COUNT(*) FROM sqlite_master WHERE name = ' . $this->failnet->db->quote('ignore'))->fetchColumn();
		try
		{
			$this->failnet->db->beginTransaction();
			if(!$table_exists)
			{
				// Attempt to install the tables
				display(' -  Creating ignored users table...');
				$this->failnet->db->exec(file_get_contents(FAILNET_ROOT . 'includes/schemas/ignore.sql'));
			}

			// Ignored hostmasks table
			$this->failnet->build_sql('ignore', 'create', 'INSERT INTO ignore ( ignore_date, hostmask ) VALUES ( :timestamp, :hostmask )');
			$this->failnet->build_sql('ignore', 'delete', 'DELETE FROM ignore WHERE LOWER(hostmask) = LOWER(:hostmask)');
			$this->failnet->build_sql('ignore', 'get_single', 'SELECT * FROM ignore WHERE LOWER(hostmask) = LOWER(:hostmask) LIMIT 1');
			$this->failnet->build_sql('ignore', 'get', 'SELECT * FROM ignore');

			$this->failnet->db->commit();
		}
		catch (PDOException $e)
		{
			// Something went boom.  Time to panic!
			$this->failnet->db->rollBack();
			if(file_exists(FAILNET_ROOT . 'data/restart.inc')) 
				unlink(FAILNET_ROOT . 'data/restart.inc');
			trigger_error($e, E_USER_WARNING);
			sleep(3);
			exit(1);
		}
		$this->load();
	}

	/**
	 * Loads in the list of ignored users and caches it
	 * @return void
	 */
	public function load()
	{
		display('=== Loading ignored users list...');
		$this->failnet->sql('ignore', 'get')->execute();
		$this->users = $this->failnet->sql('ignore', 'get')->fetchAll(PDO::FETCH_COLUMN, 0);
		$this->cache = hostmasks_to_regex($this->users);
	}

	/**
	 * Checks to see if the specified hostmask is ignored
	 * @param string $target - The hostmask to check
	 * @return boolean - True if the hostmask is ignored, false if not.
	 */
	public function ignored($target)
	{
		// Are _any_ hostmasks ignored?
		if(empty($this->users))
			return false;
		return preg_match($this->cache, $target); 
	}

	/**
	 * Adds the specified target user to the ignored users list.
	 * @param string $hostmask - The sender's hostmask
	 * @param string $target - The target hostmask that should be added to the ignore list
	 * @return True on success, false on hostmask already being ignored, NULL if not authed for this
	 */
	public function add_ignore($hostmask, $target)
	{
		if ($this->failnet->auth->authlevel($hostmask) < 10)
			return NULL;

		// Check to see if this user would already be ignored...
		if(!$this->ignored($target))
		{
			// Do that SQL thang
			$this->failnet->sql('ignore', 'create')->execute(array(':timestamp' => time(), ':hostmask' => $target));

			// Now we need to rebuild the cached PCRE pattern
			$this->users[] = $target;
			$this->cache = hostmasks_to_regex($this->users);
			return true; 
		}
		else
		{
			return false;
		}
	}

	/**
	 * Removes a specified hostmask pattern from the ignored users list.
	 * @param string $hostmask - The sender's hostmask
	 * @param string $target - The target hostmask to be removed from the ignore list
	 * @return True on success, false on hostmask not within the ignore list, NULL if not authed for this
	 */
	public function del_ignore($hostmask, $target)
	{
		if ($this->failnet->auth->authlevel($hostmask) < 10)
			return NULL;

		// Check to see if this hostmask IS in the ignored list
		if(in_array($this->users, $target))
		{
			// Do that SQL thang
			$this->failnet->sql('ignore', 'delete')->execute(array(':hostmask' => $target));

			// Now we need to rebuild the cached PCRE pattern
			foreach($this->users as $i => $user)
			{
				if($target === $user)
					unset($this->users[$i]);
			}
			$this->cache = hostmasks_to_regex($this->users);
			return true;
		}
		else
		{
			return false;
		}
	}
}

?>