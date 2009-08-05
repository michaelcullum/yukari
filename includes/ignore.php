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
	
	public $users = array();
	
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
			$this->failnet->build_sql('ignore', 'create', 'INSERT INTO ignore ( ignore_date, hostmask ) VALUES ( :timestamp, ":hostmask" )');
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

}

?>
}