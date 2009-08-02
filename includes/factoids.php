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
 * @todo Rewrite for PDO
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
	private $factoids = array();
	
	/**
	 * How many factoids processed?
	 * @var integer
	 */
	private $done = 0;
	
	/**
	 * Only one factoid at a time?
	 * @var boolean
	 */
	private $return = false;
	
/**
 * Methods
 */
	
	/**
	 * Failnet class initiator
	 * 
	 * @see includes/failnet_common#init()
	 * @return void
	 */
	public function init()
	{
		$table_exists = $this->failnet->db->query('SELECT COUNT(*) FROM sqlite_master WHERE name = ' . $this->failnet->db->quote('factoids'))->fetchColumn();
		if(!$table_exists)
		{
			// Attempt to install the factoids tables
			try
			{
				$this->failnet->db->beginTransaction();
				display(' -  Creating factoids table...');
				$this->failnet->db->exec(file_get_contents(FAILNET_ROOT . 'includes/schemas/factoids.sql'));
				display(' -  Creating entries table...');
				$this->failnet->db->exec(file_get_contents(FAILNET_ROOT . 'includes/schemas/entries.sql'));
				$this->failnet->db->commit();
			}
			catch (PDOException $e)
			{
				// Something went boom.  Time to panic!
				$this->db->rollBack();
				if(file_exists(FAILNET_ROOT . 'data/restart.inc')) 
					unlink(FAILNET_ROOT . 'data/restart.inc');
				display($error);
				sleep(3);
				exit(1);
			}
		}

		// Factoids table
		$this->failnet->build_sql('factoids', 'create', 'INSERT INTO factoids ( direct, pattern ) VALUES ( :direct, ":pattern" )');
		$this->failnet->build_sql('factoids', 'set_direct', 'UPDATE factoids SET direct = :direct WHERE factoid_id = :id');
		$this->failnet->build_sql('factoids', 'set_pattern', 'UPDATE factoids SET pattern = ":pattern" WHERE factoid_id = :id');
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

		display('=== Loading Failnet factoids index...');
		$this->load();
	}
	
	// Method to (re)load the factoids DB.
	public function load()
	{
		// @todo Overhaul this so that it will load the index of factoid patterns
	}
	
	/**
	 * Adds a factoid to the new factoids file. :D
	 */
	public function add_factoid($type, $pattern, array $factoids, $authlevel = false, $selfcheck = false, $function = false)
	{
		// @todo Rewrite for PDO
	}
	
	/**
	 * Add factoids on the fly.
	 */
	public function add_live_factoid($type, $pattern, array $factoids, $authlevel = false, $selfcheck = false, $function = false)
	{
		// @todo Rewrite for PDO
	}
	
	/**
	 * Check for matching factoids that apply to what our input is.
	 * @param string $tocheck - The message to check for factoid matching.
	 * @param string $sender - Who sent the message we are checking.
	 * @return void
	 */
	public function check($tocheck, $sender = '[unknown]')
	{
		// @todo Move to the factoids shell plugin
		// @todo Rewrite for PDO
		$this->done = 0;
		$this->return = false;
		$tocheck = str_replace('#', '\#', rtrim($tocheck));
		if (preg_match('#^' . $this->failnet->nick . '#i', $tocheck))
		{
			$forme = true;
			$command = false;
			$tocheck = preg_replace('#^' . $this->failnet->get('nick') . '(|:|,) #is', '', $tocheck);
		}
		else
		{
			$forme = false;
			$command = (preg_match('#^\|#', $tocheck)) ? true : false;
		}
		
		// Which factoid set will we use?
		if ($forme)
		{
			$facts = array_merge($this->factoids, $this->my_factoids);
		}
		elseif ($command)
		{
			$facts = array_merge($this->factoids, $this->commands);
		}
		else
		{
			$facts = $this->factoids;
		}
		
		// Prep the search/replace stuffs.
		$search = array('_nick_', '_owner_');
		$replace = array($this->failnet->get('nick'), $this->failnet->get('owner'));
		if ($sender != '[unknown]')
			$search[] = '_sender_'; $replace[] = $sender;
		
		// Scan for matching factoids!
		foreach($facts as $i => $fact)
		//for ($i = 0; $i < sizeof($facts); $i++)
		{
			$fact['pattern'] = str_replace($search, $replace, $fact['pattern']);
			   
			if ($fact['function'] == true)
			{
				if (preg_match('#' . $fact['pattern'] . '#is', $tocheck, $matches))
				{
					/* WTH is this?
					for ($j = 0; $j < sizeof($fact['factoids']); $j++)
					{
						$fact['factoids'][$j] = preg_replace('#\["#', '\"', $fact['factoids'][$j]);
					}
					*/
					if (sizeof($fact['factoids']) > 1)
					{
						$usefact = $fact['factoids'][rand(0, sizeof($fact['factoids']) - 1)];
						if (strpos($usefact, '_skip_') !== false)
						{
							eval($usefact);
							$this->done();
						}
					}
					else
					{
						eval($fact['factoids'][0]);
						$this->done();
					}
				}
			}
			else
			{
				if (preg_match('#' . $fact['pattern'] . '#is', $tocheck))
				{
					if (sizeof($fact['factoids']) > 1)
					{
						$usefact = $fact['factoids'][rand(0, sizeof($fact['factoids']) - 1)];
						if (strpos($usefact, '_action_') === 0)
						{
							$this->failnet->irc->action(preg_replace('#' . $fact['pattern'] . '#is', preg_replace('/^#_action\_#i', '', $usefact), $tocheck));
							$this->done();
						}
						elseif (strpos($usefact, '_skip_') === false)
						{
							$this->failnet->irc->privmsg(preg_replace('#' . $fact['pattern'] . '#is', $usefact, $tocheck));
							$this->done();
						}
					}
					else
					{
						if (strpos($fact['factoids'][0], '_action_') === 0)
						{
							$this->failnet->irc->action(preg_replace('#' . $fact['pattern'] . '#is', preg_replace('#^\_action\_#i', '', $fact['factoids'][0]), $tocheck));
							$this->done();
						}
						elseif (strpos($fact['factoids'][0], '_skip_') === false)
						{
							$this->failnet->irc->privmsg(preg_replace('#' . $fact['pattern'] . '#is', $fact['factoids'][0], $tocheck));
							$this->done();
						}
					}
				}
			}
			if($this->return = true)
				return;
		}
		if ($forme && $this->done == 0)
			return $this->failnet->no_factoid();
	}
	
	/**
	 * Helper function for failnet_factoids::check()
	 * @return void
	 */
	public function done()
	{
		if($this->failnet->get('single_factoid') == true)
		{
			$this->return = true;
		}
		else
		{
			$this->done++;
		}
	}
}

?>