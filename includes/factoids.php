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
if(!defined('IN_FAILNET')) return;


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
	 * $factoids = array(
	 * 		array(
	 * 			'pattern'	=> '^fail$',
	 * 			'function'	=> false,
	 * 			'factoids'	=> array(
	 * 				'haha',
	 * 			),
	 * 		),
	 * 		array(
	 * 			'pattern'	=> '^\\o\/$',
	 * 			'function'	=> true,
	 * 			'factoids'	=> array(
	 * 				'$this->privmsg(' |'); $this->privmsg('/ \\');'
	 * 			),
	 * 		),
	 * );
	 * 
	 * @NOTE: Factoids MUST NOT be added through IRC with the function setting on, this should be modified within the file itself. 
	 * 
	 */
	
	/**
	 * @todo: Use a giant array for the data, and subarrays for each entry.  
	 * 			Special settings in one entry, a subarray of responses in another...etc...
	 * 
	 */
	public $factoids = array();
	public $commands = array();
	public $my_factoids = array();
	
	protected $done = 0;
	protected $return = false;
	
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
		include FAILNET_ROOT . 'data/new_' . $filename . '.' . PHP_EXT;
		
		foreach($factoids as $fact)
		{
			foreach($new_factoids as $fact_)
			{
				if($fact['pattern'] === $fact_['pattern'])
					$fact['factoids'] = array_merge($fact['factoids'], $fact_['factoids']);
			}
			$facts = $fact[];
		}
		
		$file = '';
		$file .= '<' . '?php' . PHP_EOL;
		$file .= '/**' . PHP_EOL . ' * Failnet - Factoid Database File' . PHP_EOL . ' * Last modified: ' . date('D m/d/Y - h:i:s A') . PHP_EOL;
		$file .= PHP_EOL . PHP_EOL . '// Here be dragons.' . PHP_EOL;
		$file .= '$factoids = ' . var_export($facts) . ';' . PHP_EOL . PHP_EOL . '?' . '>';
		file_put_contents(FAILNET_ROOT . 'data/' . $filename . '.' . PHP_EXT, $file, LOCK_EX);
		unlink(FAILNET_ROOT . 'data/new_' . $filename . '.' . PHP_EXT);
	}
	
	/**
	 * Check for matching factoids that apply to what our input is.
	 */
	public function check($tocheck, $forme = false, $command = false, $sender = '[unknown]')
	{
		$this->done = 0;
		$this->return = false;
		$tocheck = rtrim($tocheck);
		
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
		$replace = array($this->nick, $this->owner);
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