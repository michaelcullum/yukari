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
 * Failnet - Ignore handling class,
 * 		Used as Failnet's handler for ignoring users based on hostmasks.
 *
 *
 * @package nodes
 * @author Obsidian
 * @copyright (c) 2009 - 2010 -- Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_node_ignore extends failnet_common
{
	/**
	 * preg_match pattern cache used to check for an ignored user
	 * @var string
	 */
	private $cache = '';

	/**
	 * List of ignored user hostmasks, used to rebuild the preg_match ignore pattern when necessary
	 * @var array
	 */
	private $users = array();

	/**
	 * Specialized init function to allow class construction to be easier.
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	public function init()
	{
		$this->failnet->ui->ui_system('--- Loading ignored users list...');
		$this->sql('ignore', 'create', 'INSERT INTO ignore ( ignore_date, hostmask ) VALUES ( :timestamp, :hostmask )');
		$this->sql('ignore', 'delete', 'DELETE FROM ignore WHERE LOWER(hostmask) = LOWER(:hostmask)');
		$this->sql('ignore', 'get_single', 'SELECT * FROM ignore WHERE LOWER(hostmask) = LOWER(:hostmask) LIMIT 1');
		$this->sql('ignore', 'get', 'SELECT * FROM ignore');

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
		// Check to see if this hostmask IS in the ignored list
		if(in_array($target, $this->users))
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
