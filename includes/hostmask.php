<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 *	Script info:
 * Version:		2.0.0 Alpha 1
 * Copyright:	(c) 2009 - Failnet Project
 * License:		GNU General Public License - Version 2
 *
 *===================================================================
 *
 */

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
 * Failnet - Hostmask class,
 * 		Used as a class for housing hostmask data 
 * 
 *
 * @package utilities
 * @author Obsidian
 * @copyright (c) 2009 - Failnet Project
 * @license GNU General Public License - Version 2
 */
class failnet_hostmask
{
	/**
	 * @var string - The host of the hostmask
	 */
	public $host = '';

	/**
	 * @var string - The nick of the hostmask
	 */
	public $nick = '';

	/**
	 * @var string - The username of the hostmask
	 */
	public $username = '';
	
	/**
	 * Parses a string containing the entire hostmask into a new instance of this class.
	 *
	 * @param string $hostmask - Entire hostmask including the nick, username, and host components
	 * @return object failnet_hostmask - New object instance populated with the data parsed from the provided hostmask string
	 */
	public static function load($hostmask)
	{
		if(preg_match('/^([^!@]+)!(?:[ni]=)?([^@]+)@([^ ]+)/', $hostmask, $match))
		{
			list(, $nick, $username, $host) = $match; 
			return new self($nick, $username, $host);
		}
		else
		{
			trigger_error('Invalid hostmask specified: "' . $hostmask . '"', E_USER_WARNING);
		}
	}

	/**
	 * Constructor method to initialize components of the hostmask.
	 * @param string $nick - Nick of the hostmask
	 * @param string $username - Username of the hostmask
	 * @param string $host - Host of the hostmask
	 * @return void
	 */
	public function __construct($nick, $username, $host)
	{
		$this->nick = $nick;
		$this->username = $username;
		$this->host = $host;
	}

	/**
	 * Returns the hostmask for the originating server or user.
	 * @return string - The full hostmask desired
	 */
	public function __toString()
	{
		return $this->nick . '!' . $this->username . '@' . $this->host;
	}
}

?>