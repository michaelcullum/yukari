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
	 * List of factoids loaded
	 * @var array
	 */
	public $factoids = array();
	
	/**
	 * List of Failnet commands
	 * @var array
	 */
	public $commands = array();
	
	/**
	 * List of factoids to trigger only when Failnet is specifically being spoken to
	 * @var array
	 */
	public $my_factoids = array();
	
	/**
	 * How many factoids processed?
	 * @var integer
	 */
	protected $done = 0;
	
	/**
	 * Only one factoid at a time?
	 * @var boolean
	 */
	protected $return = false;
	
/**
 * Constants
 */
	
	/**
	 * Create new factoid indicator
	 * @var constant
	 */
	const TYPE_NEW = 1;
	
	/**
	 * Add factoid entry indicator
	 * @var constant
	 */
	const TYPE_ADD = 2;
	
	/**
	 * Edit factoid entry indicator
	 * @var constant
	 */
	const TYPE_EDIT = 3;
	
	/**
	 * Change factoid settings indicator
	 * @var constant
	 */
	const TYPE_UPDATE = 4;

	/**
	 * Change the pattern for a factoid
	 * @var constant
	 */
	const TYPE_RESET = 5;
	
	/**
	 * Delete factoid indicator
	 * @var constant
	 */
	const TYPE_DELETE = 6;
	
	/**
	 * Remove factoid entry indicator
	 * @var constant
	 */
	const TYPE_REMOVE = 7;
	
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
		display('=== Loading factoids database');
		$this->load();
	}
	
	// Method to (re)load the factoids DB.
	public function load()
	{
		if(file_exists(FAILNET_ROOT . 'data/update_factoids.' . PHP_EXT)) 
			$this->merge('factoids');
		if(file_exists(FAILNET_ROOT . 'data/update_commands.' . PHP_EXT)) 
			$this->merge('commands');
		if(file_exists(FAILNET_ROOT . 'data/update_my_factoids.' . PHP_EXT)) 
			$this->merge('my_factoids');
		
		include FAILNET_ROOT . 'data/factoids.' . PHP_EXT;
		$this->factoids = $factoids;
		
		include FAILNET_ROOT . 'data/commands.' . PHP_EXT;
		$this->commands = $commands;
		
		include FAILNET_ROOT . 'data/my_factoids.' . PHP_EXT;
		$this->my_factoids = $my_factoids;
	}
	
	/**
	 * Merges in the factoids update file. We do this in case Failnet crashed unexpectedly...
	 * 		normally on shutdown Failnet should write the current of factoids anyways and unlink the update file.
	 * @param string $filename - The type of factoids we will merge in
	 * @return void
	 * 
	 */
	public function merge($filename) // @todo Rewrite this shiz for new update system.
	{
		include FAILNET_ROOT . 'data/' . $filename . '.' .  PHP_EXT;
		$new_factoids = $this->parse_update($filename);
		
		foreach($factoids as $fact)
		{
			foreach($new_factoids as $fact_)
			{
				// If the factoid already exists, we won't overwrite the settings, just merge in the entries.
				if($fact['pattern'] === $fact_['pattern']) 
				{
					$fact['authlevel'] = $fact_['authlevel'];
					$fact['selfcheck'] = (bool) $fact_['selfcheck'];
					$fact['function'] = (bool) $fact_['function']; 
					$fact['factoids'] = (array) array_merge($fact['factoids'], $fact_['factoids']);
				}
			}
			$facts = $fact[];
		}
		
		$file = '';
		$file .= '<' . '?php' . PHP_EOL;
		$file .= '/**' . PHP_EOL . ' * Failnet - Factoid Database File' . PHP_EOL . ' * Last modified: ' . date('D m/d/Y - h:i:s A') . PHP_EOL . ' */' . PHP_EOL;
		$file .= PHP_EOL . PHP_EOL . '// Here be dragons.' . PHP_EOL;
		$file .= '$factoids = ' . var_export($facts) . ';' . PHP_EOL . PHP_EOL . '?' . '>';
		file_put_contents(FAILNET_ROOT . 'data/' . $filename . '.' . PHP_EXT, $file, LOCK_EX);
		unlink(FAILNET_ROOT . 'data/update_' . $filename);
	}
	
	/**
	 * Parses the factoids update file and returns what updates should be carried out on the factoids database
	 */
	public function parse_update($filename)
	{
		$data = file(FAILNET_ROOT . 'data/update_' . $filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach($data as $i => $item)
		{
			$fact = explode('::', $item);
			$type = array_shift($fact);
			switch($type)
			{
				case TYPE_NEW:
					$return[$i]['type'] = $type;
					$return[$i]['pattern'] = (string) array_shift($fact);
					$authlevel = array_shift($fact);
					$return[$i]['authlevel'] = ($authlevel != 'NULL') ? (int) $authlevel : NULL;
					$return[$i]['selfcheck'] = (bool) array_shift($fact);
					$return[$i]['function'] = (bool) array_shift($fact);
					$return[$i]['factoids'] = (array) $fact;
				break;

				case TYPE_ADD:
					$return[$i]['type'] = $type;
					$return[$i]['pattern'] = (string) array_shift($fact);
					$return[$i]['factoids'] = (array) $fact;
				break;

				case TYPE_EDIT:
					$return[$i]['type'] = $type;
					$return[$i]['pattern'] = (string) array_shift($fact);
					$return[$i]['old'] = (string) array_shift($fact);
					$return[$i]['new'] = (string) $fact;
				break;

				case TYPE_UPDATE:
					$return[$i]['pattern'] = (string) array_shift($fact);
					$return[$i]['authlevel'] = ($authlevel != 'NULL') ? (int) $authlevel : NULL;
					$return[$i]['selfcheck'] = (bool) array_shift($fact);
					$return[$i]['function'] = (bool) array_shift($fact);
				break;

				case TYPE_RESET:
					$return[$i]['pattern'] = (string) array_shift($fact);
					$return[$i]['new'] = (string) $fact;
				break;

				case TYPE_DELETE:
					$return[$i]['pattern'] = (string) $fact;
				break;

				case TYPE_REMOVE:
					$return[$i]['pattern'] = (string) array_shift($fact);
					$return[$i]['factoids'] = (array) $fact;  
				break;
			}
		}
		return $return;
	}
	
	/**
	 * Adds a factoid to the new factoids file. :D
	 */
	public function add_factoid($type, $pattern, array $factoids, $authlevel = false, $selfcheck = false, $function = false)
	{ // @todo Update so that this passes the right value for the update file
		$data = file(FAILNET_ROOT . 'data/update_' . $type, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$found = false;
		foreach ($data as $key => $fact)
		{
			$fact_ = explode('::', $fact);
			if($fact_[0] === $pattern)
			{
				// Just add factoids, ignore the settings.
				$fact_ = array_merge($fact_, $factoids);
				$data[$key] = implode('::', $fact_);
				$found = true;
				break;
			}
		}
		if(!$found)
		{
			$authlevel = ($authlevel != false) ? $authlevel : 'NULL';
			$data[] = $pattern . '::' . $authlevel . '::' . (($selfcheck) ? 1 : 0) . '::' . (($function) ? 1 : 0) . '::' . implode('::', $factoids);
		}
		
		$this->add_live_factoid($type, $pattern, $factoids, $authlevel, $selfcheck, $function);
		return file_put_contents(FAILNET_ROOT . 'data/new_' . $type, $data, LOCK_EX);
	}
	
	/**
	 * Add factoids on the fly.
	 */
	public function add_live_factoid($type, $pattern, array $factoids, $authlevel = false, $selfcheck = false, $function = false)
	{
		$f_found = $c_found = $my_found = false;
		switch ($type)
		{
			case 'factoids':
				foreach($this->factoids as $key => $fact)
				{
					if($fact['pattern'] !== $pattern)
						continue;

					$this->factoids[$key]['factoids'] = array_merge($this->factoids[$key]['factoids'], $factoids);
					$f_found = true;
				}
			break;
			case 'commands':
				foreach($this->commands as $key => $fact)
				{
					if($fact['pattern'] !== $pattern)
						continue;

					$this->commands[$key]['factoids'] = array_merge($this->commands[$key]['factoids'], $factoids);
					$c_found = true;
				}
			break;
			case 'my_factoids':
				foreach($this->my_factoids as $key => $fact)
				{
					if($fact['pattern'] !== $pattern)
						continue;

					$this->my_factoids[$key]['factoids'] = array_merge($this->my_factoids[$key]['factoids'], $factoids);
					$my_found = true;
				}
			break;
		}
		if(!$f_found || !$c_found || !$my_found)
		{
			// Okay, didn't find one matching, so let's make one.
			$fact_array = array(
				'pattern'	=> (string) $pattern,
				'authlevel'	=> ($authlevel) ? (int) $authlevel : NULL,
				'selfcheck'	=> (bool) $selfcheck,
				'function'	=> (bool) $function,
				'factoids'	=> (array) $factoids,
			);
			// Okay, didn't find one matching, so let's make one.
			if(!$f_found && $type == 'factoids')
				$this->factoids[] = $fact_array;
			if(!$c_found && $type == 'commands')
				$this->commands[] = $fact_array;
			if(!$my_found && $type == 'my_factoids')
				$this->my_factoids[] = $fact_array;
		}
	}
	
	/**
	 * Check for matching factoids that apply to what our input is.
	 * @param string $tocheck - The message to check for factoid matching.
	 * @param string $sender - Who sent the message we are checking.
	 * @return void
	 */
	public function check($tocheck, $sender = '[unknown]')
	{	// @todo Move to the factoids shell plugin
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