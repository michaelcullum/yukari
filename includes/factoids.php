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

// @todo failnet_factoids::no_factoid() method, for saying something when there's no factoid available for that.

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
 * Failnet - Factoid handling class,
 * 		Used as Failnet's factoid handler. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
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
	
// @todo Add entry method
// @todo Remove entry method
// @todo Change factoid method
// @todo Change factoid settings method
// @todo Change entry settings method
	
	/**
	 * Failnet class initiator
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	private function init()
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
			$this->failnet->build_sql('factoids', 'create', 'INSERT INTO factoids ( direct, pattern ) VALUES ( :direct, ":pattern" )');
			$this->failnet->build_sql('factoids', 'set_direct', 'UPDATE factoids SET direct = :direct WHERE factoid_id = :id');
			$this->failnet->build_sql('factoids', 'set_pattern', 'UPDATE factoids SET pattern = ":pattern" WHERE factoid_id = :id');
			$this->failnet->build_sql('factoids', 'get', 'SELECT * FROM factoids WHERE factoid_id = :id');
			$this->failnet->build_sql('factoids', 'get_pattern', 'SELECT * FROM factoids WHERE LOWER(pattern) = LOWER(":pattern")');
			$this->failnet->build_sql('factoids', 'get_all', 'SELECT * FROM factoids ORDER BY factoid_id DESC');
			$this->failnet->build_sql('factoids', 'delete', 'DELETE FROM factoids WHERE factoid_id = :id');

			// Entries table
			$this->failnet->build_sql('entries', 'create', 'INSERT INTO entries ( factoid_id, authlevel, selfcheck, function, entry ) VALUES ( :id, :authlevel, :selfcheck, :function, ":entry" )');
			$this->failnet->build_sql('entries', 'get', 'SELECT * FROM entries WHERE factoid_id = :id LIMIT 1');
			$this->failnet->build_sql('entries', 'rand', 'SELECT * FROM entries WHERE factoid_id = :id ORDER BY RANDOM() LIMIT 1');
			$this->failnet->build_sql('entries', 'set_authlevel', 'UPDATE entries SET authlevel = :authlevel WHERE entry_id = :entry_id');
			$this->failnet->build_sql('entries', 'set_authlevel', 'UPDATE entries SET selfcheck = :selfcheck WHERE entry_id = :entry_id');
			$this->failnet->build_sql('entries', 'set_authlevel', 'UPDATE entries SET function = :function WHERE entry_id = :entry_id');
			$this->failnet->build_sql('entries', 'set_authlevel', 'UPDATE entries SET entry = ":entry" WHERE entry_id = :entry_id');
			$this->failnet->build_sql('entries', 'delete', 'DELETE FROM entries WHERE entry_id = :entry_id');
			$this->failnet->build_sql('entries', 'delete_all', 'DELETE FROM entries WHERE factoid_id = :id');

			if(!$table_exists)
			{
				// Let's toss in a default entry
				$this->failnet->sql('factoids', 'create')->execute(array(':direct' => 1, ':pattern' => '^intro$'));
				$this->failnet->sql('factoids', 'get_pattern')->execute(array(':pattern' => '^intro$'));
				$result = $this->failnet->sql('factoids', 'get_pattern')->fetch(PDO::FETCH_ASSOC);
				$this->failnet->sql('entries', 'create')->execute(array(':id' => $result['factoid_id'], ':authlevel' => 0, ':selfcheck' => 0, ':function' => 0, ':entry' => 'Failnet 2.  Smarter, faster, and with a sexier voice than ever before.'));
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

	// @todo Add factoid method
	public function add_factoid()
	{
		
	}

	// @todo Remove factoid method
	public function delete_factoid()
	{
		
	}
}

?>