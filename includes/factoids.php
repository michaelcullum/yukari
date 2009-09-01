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
 * License:		GNU General Public License - Version 2
 *
 *===================================================================
 * 
 */

// @todo failnet_factoids::no_factoid() method, for saying something when there's no factoid available for that.

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
 * Failnet - Factoid handling class,
 * 		Used as Failnet's factoid handler. 
 * 
 *
 * @package factoids
 * @author Obsidian
 * @copyright (c) 2009 - Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_factoids extends failnet_common
{
/**
 * Properties
 */

	/**
	 * List of factoid patterns loaded for speed
	 * @var array
	 */
	public $factoids = array();

/**
 * Methods
 */

// @todo Remove entry method
// @todo Change factoid method
// @todo Change factoid settings method
// @todo Change entry settings method

	/**
	 * Failnet class initiator
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	public function init()
	{
		$table_exists = $this->failnet->db->query('SELECT COUNT(*) FROM sqlite_master WHERE name = ' . $this->failnet->db->quote('factoids'))->fetchColumn();
		try
		{
			$this->failnet->db->beginTransaction();
			if(!$table_exists)
			{
				// Attempt to install the factoids tables
				display(' -  Creating factoids table...');
				$this->failnet->db->exec(file_get_contents(FAILNET_ROOT . 'includes/schemas/factoids.sql'));
				display(' -  Creating entries table...');
				$this->failnet->db->exec(file_get_contents(FAILNET_ROOT . 'includes/schemas/entries.sql'));
			}

			// Factoids table
			$this->failnet->sql('factoids', 'create', 'INSERT INTO factoids ( direct, pattern ) VALUES ( :direct, :pattern )');
			$this->failnet->sql('factoids', 'set_direct', 'UPDATE factoids SET direct = :direct WHERE factoid_id = :id');
			$this->failnet->sql('factoids', 'set_pattern', 'UPDATE factoids SET pattern = :pattern WHERE factoid_id = :id');
			$this->failnet->sql('factoids', 'get', 'SELECT * FROM factoids WHERE factoid_id = :id');
			$this->failnet->sql('factoids', 'get_pattern', 'SELECT * FROM factoids WHERE LOWER(pattern) = LOWER(:pattern)');
			$this->failnet->sql('factoids', 'get_all', 'SELECT * FROM factoids ORDER BY factoid_id DESC');
			$this->failnet->sql('factoids', 'delete', 'DELETE FROM factoids WHERE factoid_id = :id');

			// Entries table
			$this->failnet->sql('entries', 'create', 'INSERT INTO entries ( factoid_id, authlevel, selfcheck, is_function, entry ) VALUES ( :id, :authlevel, :selfcheck, :function, :entry )');
			$this->failnet->sql('entries', 'get', 'SELECT * FROM entries WHERE factoid_id = :id');
			$this->failnet->sql('entries', 'get_entry', 'SELECT * FROM entries WHERE entry_id = :id AND LIMIT 1');
			$this->failnet->sql('entries', 'rand', 'SELECT * FROM entries WHERE factoid_id = :id ORDER BY RANDOM() LIMIT 1');
			$this->failnet->sql('entries', 'set_authlevel', 'UPDATE entries SET authlevel = :authlevel WHERE entry_id = :entry_id');
			$this->failnet->sql('entries', 'set_selfcheck', 'UPDATE entries SET selfcheck = :selfcheck WHERE entry_id = :entry_id');
			$this->failnet->sql('entries', 'set_function', 'UPDATE entries SET is_function = :function WHERE entry_id = :entry_id');
			$this->failnet->sql('entries', 'set_entry', 'UPDATE entries SET entry = :entry WHERE entry_id = :entry_id');
			$this->failnet->sql('entries', 'delete', 'DELETE FROM entries WHERE entry_id = :entry_id');
			$this->failnet->sql('entries', 'delete_factoid_id', 'DELETE FROM entries WHERE factoid_id = :id');

			if(!$table_exists)
			{
				// Let's toss in a default entry
				$id = $this->add_factoid('^intro$', 1);
				$this->add_entry($id, 'Failnet 2.0 -- Smarter, faster, and with a sexier voice than ever before.', 0, false, false);
			}

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

		// Load the factoids index
		$this->load();
	}

	/**
	 * Loads the index of factoids and caches it
	 * @return void
	 */
	private function load()
	{
		display('=== Loading Failnet factoids index...');
		$this->failnet->sql('factoids', 'get_all')->execute();
		$this->factoids = $this->failnet->sql('factoids', 'get_all')->fetchAll();
	}

	/**
	 * Create a new factoid (but does not add the initial entry!)
	 * @param string $pattern - The PCRE pattern for the factoid
	 * @param boolean $direct - Should this only be triggered if Failnet is the targeted recipient?
	 * @return mixed - Integer with the factoid's ID if successful, false if creation failed
	 */
	public function add_factoid($pattern, $direct)
	{
		$this->failnet->sql('factoids', 'create')->execute(array(':pattern' => $pattern, ':direct' => $direct));

		// Grab the ID for this factoid
		$this->failnet->sql('factoids', 'get_pattern')->execute(array(':pattern' => $pattern));
		$result = $this->failnet->sql('factoids', 'get_pattern')->fetch(PDO::FETCH_ASSOC);

		// Do we have anything with that pattern?  If not, something went boom.
		if(!$result)
			return false;

		return (int) $result['factoids_id'];
	}

	/**
	 * Delete a factoid from the database
	 * @param string $pattern - Pattern for the factoid that we want to delete
	 * @return mixed - NULL if no such factoid, true if deletion successful.
	 */
	public function delete_factoid($pattern)
	{
		$this->failnet->sql('factoids', 'get_pattern')->execute(array(':pattern' => $pattern));
		$result = $this->failnet->sql('factoids', 'get_pattern')->fetch(PDO::FETCH_ASSOC);

		// Do we have anything with that pattern?
		if(!$result)
			return NULL;

		// Let's delete stuff now, including any entries we had there
		$this->failnet->sql('factoids', 'delete')->execute(array(':id' => $result['factoid_id']));
		$this->failnet->sql('entries', 'delete_factoid_id')->execute(array(':pattern' => $pattern));
		return true;
	}

	/**
	 * Change a factoid's pattern
	 * @param string $pattern - Pattern for the factoid that we want to change the pattern for
	 * @param string $new_pattern - The new pattern we want to use
	 * @return mixed - NULL if no such factoid, true if change successful.
	 */
	public function edit_factoid($pattern, $new_pattern)
	{
		$this->failnet->sql('factoids', 'get_pattern')->execute(array(':pattern' => $pattern));
		$result = $this->failnet->sql('factoids', 'get_pattern')->fetch(PDO::FETCH_ASSOC);

		// Do we have anything with that pattern?
		if(!$result)
			return NULL;

		// Time to change that pattern
		$this->failnet->sql('factoids', 'set_pattern')->execute(array(':pattern' => $new_pattern, ':id' => $result['factoid_id']));
		return true;
	}

	/**
	 * Changes whether or not a factoid will only trigger if Failnet specifically is addressed
	 * @param string $pattern - Pattern for the factoid that we want to change the direct setting for
	 * @param boolean $direct - Should the factoid only trigger if Failnet is specifically addressed?
	 * @return mixed - NULL if no such factoid, true if change successful.
	 */
	public function set_direct($pattern, $direct)
	{
		$this->failnet->sql('factoids', 'get_pattern')->execute(array(':pattern' => $pattern));
		$result = $this->failnet->sql('factoids', 'get_pattern')->fetch(PDO::FETCH_ASSOC);

		// Do we have anything with that pattern?
		if(!$result)
			return NULL;

		$this->failnet->sql('factoids', 'set_direct')->execute(array(':direct' => (bool) $direct, ':id' => $result['factoid_id']));
	}

	/**
	 * Creates a new entry for a specified factoid
	 * @param integer $factoid_id - The factoid ID that this should be set for
	 * @param string $entry - The entry that we want to put in
	 * @param integer $authlevel - The authlevel it takes to use this entry
	 * @param boolean $selfcheck - Do we check if this includes our name or our owner's name?
	 * @param boolean $function - Do we run this through eval() or not?
	 * @return boolean - True on success
	 */
	public function add_entry($factoid_id, $entry, $authlevel, $selfcheck = false, $function = false)
	{
		$selfcheck = ((bool) $selfcheck === true) ? 1 : 0;
		$function = ((bool) $function === true) ? 1 : 0;
		$this->failnet->sql('entries', 'create')->execute(array(':id' => (int) $factoid_id, ':authlevel' => (int) $authlevel, ':selfcheck' => $selfcheck, ':function' => $function, ':entry' => trim($entry)));
		return true;
	}

	/**
	 * Deletes an entry for a factoid in the database
	 * @param integer $entry_id - The ID of the entry that we want to delete
	 * @return mixed - NULL if no such factoid entry, true if deletion successful.
	 */
	public function delete_entry($entry_id)
	{
		$this->failnet->sql('entries', 'get_entry')->execute(array(':id' => $entry_id));
		$result = $this->failnet->sql('entries', 'get_entry')->fetch(PDO::FETCH_ASSOC);

		// Do we have anything with that ID?
		if(!$result)
			return NULL;

		// Let's delete some shiz.
		$this->failnet->sql('entries', 'delete')->execute(array(':id' => $entry_id));
	}

	public function edit_entry()
	{
		
	}

	public function set_entry()
	{
		
	}
}

?>