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
 * Failnet - User authorization handling class,
 * 		Used as Failnet's authorization handler. 
 * 
 * 
 * @author Obsidian
 * @copyright (c) 2009 - Obsidian
 * @license http://opensource.org/licenses/gpl-2.0.php | GNU Public License v2
 */
class failnet_auth extends failnet_common
{
	public $hmask_find = array('\\',   '^',   '$',   '.',   '[',   ']',   '|',   '(',   ')',   '?',   '+',   '{',   '}');
	public $hmask_repl = array('\\\\', '\\^', '\\$', '\\.', '\\[', '\\]', '\\|', '\\(', '\\)', '\\?', '\\+', '\\{', '\\}');
	
	public function init()
	{
		display('=---= Loading user database'); 
			$this->load();
	}
	
	/**
	 * Parses a IRC hostmask and sets nick, user and host bits.
	 *
	 * @param string $hostmask Hostmask to parse
	 * @param string $nick Container for the nick
	 * @param string $user Container for the username
	 * @param string $host Container for the hostname
	 * @return void
	 * 
	 * @author Phergie Development Team {@link http://code.assembla.com/phergie/subversion/nodes}
	 */
	public function parse_hostmask($hostmask, &$nick, &$user, &$host)
	{
		if (preg_match('/^([^!@]+)!([^@]+)@(.*)$/', $hostmask, $match) > 0)
		{
			list(, $nick, $user, $host) = array_pad($match, 4, null);
		}
		else
		{
			$host = $hostmask;
		}
	}

	/**
	 * Converts a delimited string of hostmasks into a regular expression
	 * that will match any hostmask in the original string.
	 *
	 * @param string $list Delimited string of hostmasks
	 * @return string Regular expression
	 * 
	 * @author Phergie Development Team {@link http://code.assembla.com/phergie/subversion/nodes}
	 */
	public function hostmasks_to_regex($list)
	{
		$patterns = array();

		foreach(preg_split('#[\s\r\n,]+#', $list) as $hostmask)
		{
			// Find out which chars are present in the config mask and exclude them from the regex match
			$excluded = '';
			if (strpos($hostmask, '!') !== false)
			{
				$excluded .= '!';
			}
			if (strpos($hostmask, '@') !== false)
			{
				$excluded .= '@';
			}

			// Escape regex meta characters
			$hostmask = str_replace($this->hmask_find, $this->hmask_repl, $hostmask);

			// Replace * so that they match correctly in a regex
			$patterns[] = str_replace('*', ($excluded === '' ? '.*' : '[^' . $excluded . ']*'), $hostmask);
		}

		return ('#^' . implode('|', $patterns) . '$#i');
	}
	
}

?>