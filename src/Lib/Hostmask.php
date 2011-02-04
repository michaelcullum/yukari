<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Yukari
 * @package     lib
 * @author      Damian Bushong
 * @copyright   (c) 2009 - 2011 -- Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 *
 *===================================================================
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 */

namespace Yukari\Lib;

/**
 * Yukari - Hostmask class,
 * 	    Used as an object for housing hostmask data
 *
 *
 * @category    Yukari
 * @package     lib
 * @author      Damian Bushong
 * @license     MIT License
 * @link        https://github.com/damianb/yukari
 */
class Hostmask implements \ArrayAccess
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
	 * @return object \Yukari\Lib\Hostmask - New object instance populated with the data parsed from the provided hostmask string
	 */
	public static function load($hostmask)
	{
		if(!preg_match('/^([^!@]+)!(?:[ni]=)?([^@]+)@([^ ]+)/', $hostmask, $match))
			throw new \LogicException(sprintf('Invalid hostmask "%s" specified', $hostmask));

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
	 *
	 * @throws \RuntimeException
	 */
	public function offsetSet($offset, $value)
	{
		if(!property_exists($this, $offset))
			throw new \RuntimeException('Attempt to access an invalid property in a hostmask object failed');

		$this->$offset = $value;
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
