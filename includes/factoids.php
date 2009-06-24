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
 * @TODO: Removing factoids...
 * @TODO: Removing factoid entries...
 * @TODO: Changing factoid settings...
 * 
 * @TODO: Write a plugin for this?  Either convert to a plugin or use a plugin as a shell.
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
if(!defined('IN_FAILNET')) exit;


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
	 * Factoid array structure...
	 * 
	 * <code>
	 * $factoids = array(
	 * 		array(
	 * 			'pattern'	=> '^fail$',
	 * 			'authlevel'	=> NULL,
	 * 			'selfcheck'	=> false,
	 * 			'function'	=> true,
	 * 			'factoids'	=> array(
	 * 				'haha',
	 * 			),
	 * 		),
	 * 		array(
	 * 			'pattern'	=> '^\\o\/$',
	 * 			'authlevel'	=> NULL,
	 * 			'selfcheck'	=> false,
	 * 			'function'	=> true,
	 * 			'factoids'	=> array(
	 * 				'$this->privmsg(' |'); $this->privmsg('/ \\');'
	 * 			),
	 * 		),
	 * );
	 * </code>
	 * 
	 */

	public $factoids = array();
	public $commands = array();
	public $my_factoids = array();
	
	protected $done = 0;
	protected $return = false;
	
	public function init()
	{
		display('=== Loading factoids database')
		$this->load();
	}
	
	// Method to (re)load the factoids DB.
	public function load()
	{
		if(file_exists(FAILNET_ROOT . 'data/new_factoids.' . PHP_EXT)) 
			$this->merge('factoids');
		if(file_exists(FAILNET_ROOT . 'data/new_commands.' . PHP_EXT)) 
			$this->merge('commands');
		if(file_exists(FAILNET_ROOT . 'data/new_my_factoids.' . PHP_EXT)) 
			$this->merge('my_factoids');
		
		include FAILNET_ROOT . 'data/factoids.' . PHP_EXT;
		$this->factoids = $factoids;
		
		include FAILNET_ROOT . 'data/commands.' . PHP_EXT;
		$this->commands = array_merge($commands, $this->factoids);
		
		include FAILNET_ROOT . 'data/my_factoids.' . PHP_EXT;
		$this->my_factoids = array_merge($my_factoids, $this->factoids);
	}
	
	/**
	 *  Merges in the new factoids file. ;)
	 */
	public function merge($filename)
	{
		include FAILNET_ROOT . 'data/' . $filename . '.' .  PHP_EXT;
		$new_factoids = $this->parse_new_factoids($filename)
		
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
		unlink(FAILNET_ROOT . 'data/new_' . $filename);
	}
	
	/**
	 * Parses the new factoids file and returns what the new factoids are.
	 */
	public function parse_new_factoids($filename)
	{
		$data = file(FAILNET_ROOT . 'data/new_' . $filename);
		for ($i = 0; $i < sizeof($data); $i++)
		{
			$fact_ = explode('::', $data[$i]);
			$return[$i]['pattern'] = (string) array_shift($fact_);
			$authlevel = array_shift($fact_);
			$return[$i]['authlevel'] = ($authlevel != 'NULL') ? (int) $authlevel : NULL;
			$return[$i]['selfcheck'] = (bool) array_shift($fact_);
			$return[$i]['function'] = (bool) array_shift($fact_);;
			$return[$i]['factoids'] = (array) $fact_;
		}
		return $return;
	}
	
	/**
	 * Adds a factoid to the new factoids file. :D
	 */
	public function add_factoid($type, $pattern, array $factoids, $authlevel = false, $selfcheck = false, $function = false)
	{
		$data = file(FAILNET_ROOT . 'data/new_' . $type);
		$found = false;
		foreach ($data as $key => $fact)
		{
			$fact_ = explode('::', $fact);
			if($fact_[0] === $pattern)
			{
				// Just add factoids, ignore the settings.
				$fact_ = array_merge($fact_, $factoids);
				$data[$key] = implode('::', $fact_)
				$found = true;
				break;
			}
		}
		if(!$found)
		{
			$authlevel = ($authlevel != false) ? $authlevel : 'NULL';
			$data[] = $pattern . '::' . $authlevel . '::' . (($selfcheck) ? 1 : 0) . '::' . (($function) ? 1 : 0) . '::' implode('::', $factoids);
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
					{
						continue;
					}
					$this->factoids[$key]['factoids'] = array_merge($this->factoids[$key]['factoids'], $factoids);
					$f_found = true;
				}
				foreach($this->commands as $key => $fact)
				{
					if($fact['pattern'] !== $pattern)
					{
						continue;
					}
					$this->commands[$key]['factoids'] = array_merge($this->commands[$key]['factoids'], $factoids);
					$c_found = true;
				}
				foreach($this->my_factoids as $key => $fact)
				{
					if($fact['pattern'] !== $pattern)
					{
						continue;
					}
					$this->my_factoids[$key]['factoids'] = array_merge($this->my_factoids[$key]['factoids'], $factoids);
					$my_found = true;
				}
			break;
			case 'commands':
				foreach($this->commands as $key => $fact)
				{
					if($fact['pattern'] !== $pattern)
					{
						continue;
					}
					$this->commands[$key]['factoids'] = array_merge($this->commands[$key]['factoids'], $factoids);
					$c_found = true;
				}
			break;
			case 'my_factoids':
				foreach($this->my_factoids as $key => $fact)
				{
					if($fact['pattern'] !== $pattern)
					{
						continue;
					}
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
			if(!$c_found && ($type == 'factoids' || $type == 'commands'))
				$this->commands[] = $fact_array;
			if(!$my_found && ($type == 'factoids' || $type == 'my_factoids'))
				$this->my_factoids[] = $fact_array;
		}
	}
	
	/**
	 * Check for matching factoids that apply to what our input is.
	 */
	public function check($tocheck, $forme = false, $command = false, $sender = '[unknown]')
	{
		$this->done = 0;
		$this->return = false;
		$tocheck = rtrim($tocheck);
		if (preg_match('/^' . $this->failnet->nick . '/i', $tocheck))
		{
			$forme = true;
			$command = false;
			$tocheck = preg_replace('/^' . $this->failnet->nick . '(|:|,) /i', '', $tocheck);
		}
		else
		{
			$forme = false;
			$command = (preg_match('/^\|/', $tocheck)) ? true : false;
		}
		
		// Which factoid set will we use?
		if ($forme)
		{
			$facts = $this->my_factoids;
		}
		elseif ($command)
		{
			$facts = $this->commands;
		}
		else
		{
			$facts = $this->factoids;
		}
		
		// Prep the search/replace stuffs.
		$search = array('_nick_', '_owner_');
		$replace = array($this->failnet->nick, $this->failnet->owner);
		if ($sender != '[unknown]')
		{
			$search[] = '_sender_'; $replace[] = $sender;
		}
		
		// Scan for matching factoids!
		for ($i = 0; $i < sizeof($facts); $i++)
		{
			$facts[$i]['pattern'] = str_replace($search, $replace, $facts[$i]['pattern']);
			   
			if ($facts[$i]['function'] == true)
			{
				if (preg_match('/' . $facts[$i]['pattern'] . '/is', $tocheck, $matches))
				{
					for ($j = 0; $j < sizeof($facts[$i]['factoids']); $j++)
					{
						$facts[$i]['factoids'][$j] = preg_replace('/\["/', '\"', $facts[$i]['factoids'][$j]);
					}
					if (sizeof($facts[$i]['factoids']) > 1)
					{
						$usefact = $facts[$i]['factoids'][rand(0, sizeof($facts[$i]['factoids']) - 1)];
						if (!ereg('_skip_', $usefact))
						{
							eval($usefact);
							$this->done();
						}
					}
					else
					{
						eval($facts[$i]['factoids'][0]);
						$this->done();
					}
				}
			}
			else
			{
				if (preg_match('/' . $facts[$i]['pattern'] . '/is', $tocheck))
				{
					if (sizeof($facts[$i]['factoids']) > 1)
					{
						$usefact = $facts[$i]['factoids'][rand(0, sizeof($facts[$i]['factoids']) - 1)];
						if (preg_match('/^\_action\_/i', $usefact))
						{
							$this->failnet->irc->action(preg_replace('/' . $facts[$i]['pattern'] . '/is', preg_replace('/^\_action\_/i', '', $usefact), $tocheck));
							$this->done();
						}
						else
						{
							if (!ereg('_skip_', $usefact))
							{
								$this->failnet->irc->privmsg(preg_replace('/' . $facts[$i]['pattern'] . '/is', $usefact, $tocheck));
								$this->done();
							}
						}
					}
					else
					{
						if (preg_match('/^\_action\_/i', $facts[$i]['factoids'][0]))
						{
							$this->failnet->irc->action(preg_replace('/' . $facts[$i]['pattern'] . '/is', preg_replace('/^\_action\_/i', '', $facts[$i]['factoids'][0]), $tocheck));
							$this->done();
						}
						else
						{
							if (!ereg('_skip_', $facts[$i][1]))
							{
								$this->failnet->irc->privmsg(preg_replace('/' . $facts[$i]['pattern'] . '/is', $facts[$i]['factoids'][0], $tocheck));
								$this->done();
							}
						}
					}
				}
			}
			if($this->return = true)
				return;
		}
		if ($forme && $this->done == 0)
			$this->failnet->irc->privmsg($this->failnet->no_factoid());
	}
	
	// Helper function for failnet_factoids::check()
	public function done()
	{
		if($this->failnet->single_factoid == true)
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