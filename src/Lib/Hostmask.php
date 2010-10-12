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
 * @link        http://github.com/Obsidian1510/Failnet3
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

/**
 * Failnet - Hostmask class,
 * 	    Used as an object for housing hostmask data
 *
 *
 * @category    Failnet
 * @package     libs
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
class Hostmask extends Failnet\Base implements \ArrayAccess
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

	/**
	 * ArrayAccess stuff
	 */

	/**
	 * Check if an "array" offset exists in this object.
	 * @param mixed $offset - The offset to check.
	 * @return boolean - Does anything exist for this offset?
	 */
	public function offsetExists($offset)
	{
		return property_exists($this, $offset);
	}

	/**
	 * Get an "array" offset for this object.
	 * @param mixed $offset - The offset to grab from.
	 * @return mixed - The value of the offset, or null if the offset does not exist.
	 */
	public function offsetGet($offset)
	{
		return property_exists($this, $offset) ? $this->$offset : NULL;
	}

	/**
	 * Set an "array" offset to a certain value, if the offset exists
	 * @param mixed $offset - The offset to set.
	 * @param mixed $value - The value to set to the offset.
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		if(property_exists($this, $offset))
		{
			$this->$offset = $value;
		}
		else
		{
			throw new HostmaskException('Attempt to access an invalid property in a hostmask object failed', HostmaskException::ERR_INVALID_PROPERTY);
		}
	}

	/**
	 * Unset an "array" offset.
	 * @param mixed $offset - The offset to clear out.
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		$this->$offset = NULL;
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
 * @link        http://github.com/Obsidian1510/Failnet3
 *
 * @note reserves 300xx error codes
 */
class HostmaskException extends Failnet\FailnetException
{
	const ERR_INVALID_HOSTMASK = 30000;
	const ERR_INVALID_PROPERTY = 30001;
}
