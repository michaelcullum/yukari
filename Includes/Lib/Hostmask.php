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
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @todo ArrayAccess support
 *
 */

namespace Failnet\Lib;
use Failnet as Root;

/**
 * Failnet - Hostmask class,
 * 	    Used as an object for housing hostmask data
 *
 *
 * @category    Failnet
 * @package     libs
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Hostmask extends Root\Base
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
	 * @return object Failnet\Lib\Hostmask - New object instance populated with the data parsed from the provided hostmask string
	 *
	 * @throws Failnet\Lib\HostmaskException
	 */
	public static function load($hostmask)
	{
		if(!preg_match('/^([^!@]+)!(?:[ni]=)?([^@]+)@([^ ]+)/', $hostmask, $match))
			throw new HostmaskException(sprintf('Invalid hostmask "%1$s" specified', $hostmask), HostmaskException::ERR_INVALID_HOSTMASK);

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
		return (preg_match('#^' . str_replace('*', '.*', $regex) . '$#i', (string) $this) > 0) ? true : false;
	}
}

/**
 * Failnet - Subordinate exception class
 *      Extension of the Failnet exception class.
 *
 *
 * @category    Failnet
 * @package     Failnet
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 *
 * @note reserves 300xx error codes
 */
class HostmaskException extends Root\FailnetException
{
	const ERR_INVALID_HOSTMASK = 30000;
}
