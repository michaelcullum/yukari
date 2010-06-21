<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     libs
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2010 -- Damian Bushong
 * @license     GNU General Public License, Version 3
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Failnet\Lib;
use Failnet;

/**
 * Failnet - Hostmask class,
 * 	    Used as a class for housing hostmask data
 *
 *
 * @category    Failnet
 * @package     libs
 * @author      Damian Bushong
 * @license     GNU General Public License, Version 3
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Hostmask extends Base
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
	 * Constructor method to initialize components of the hostmask.
	 * @param string $nick - Nick of the hostmask
	 * @param string $username - Username of the hostmask
	 * @param string $host - Host of the hostmask
	 * @return void
	 */
	public function __construct($nick, $username, $host)
	{
		list($this->nick, $this->username, $this->host) = array($nick, $username, $host);
	}

	/**
	 * Parses a string containing the entire hostmask into a new instance of this class.
	 * @param string $hostmask - Entire hostmask including the nick, username, and host components
	 * @return object Failnet\Hostmask - New object instance populated with the data parsed from the provided hostmask string
	 * @throws Failnet\Exception
	 */
	public static function load($hostmask)
	{
		if(!preg_match('/^([^!@]+)!(?:[ni]=)?([^@]+)@([^ ]+)/', $hostmask, $match))
			throw new Exception(ex(Exception::ERR_INVALID_HOSTMASK, $hostmask));

		list(, $nick, $username, $host) = $match;
		return new Hostmask($nick, $username, $host);
	}

	/**
	 * Returns the hostmask for the originating server or user.
	 * @return string - The full hostmask desired
	 */
	public function __toString()
	{
		return $this->nick . '!' . $this->username . '@' . $this->host;
	}

	/**
	 * Checks for a match against this hostmask
	 * @param string $regex - The regex pattern to check.
	 * @return boolean - Does this hostmask match our pattern?
	 */
	public function checkMatch($regex)
	{
		return (preg_match('#^' . str_replace('*', '.*', $regex) . '$#i', (string) $this) > 0);
	}
}
