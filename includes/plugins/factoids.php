<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0
 * SVN ID:		$Id$
 * Copyright:	(c) 2009 - Failnet Project
 * License:		http://opensource.org/licenses/gpl-2.0.php  |  GNU Public License v2
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
 * Failnet - Factoid engine shell plugin,
 * 		The shell for the Factoid engine to be run in for Failnet. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_plugin_factoids extends failnet_plugin_common
{
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
	 * Check for matching factoids that apply to what our input is.
	 * @param string $tocheck - The message to check for factoid matching.
	 * @param string $sender - Who sent the message we are checking.
	 * @return void
	 */
	public function check($tocheck, $sender = '[unknown]')
	{
		// @todo Rewrite for new factoid engine
		// @todo Rewrite with proper plugin calls 
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
	 * Helper function for failnet_plugin_factoids::check()
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