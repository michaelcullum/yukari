<?php
/**
 *
 *===================================================================
 *
 *  Yukari
 *-------------------------------------------------------------------
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

namespace Yukari\Connection;

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
class Hostmask
{
	/**
	 * @var string - The host of the hostmask
	 */
	protected $host = '';

	/**
	 * @var string - The nick of the hostmask
	 */
	protected $nick = '';

	/**
	 * @var string - The username of the hostmask
	 */
	protected $username = '';

	/**
	 * Get a new instance of the hostmask object.
	 * @return \Yukari\Connection\Hostmask - Provides a fluent interface.
	 */
	public static function newInstance()
	{
		return new static();
	}

	/**
	 * Get the host for the current hostmask.
	 * @return string - The current host for the hostmask.
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Set the host for the current hostmask
	 * @param string $host - The host to set.
	 * @return \Yukari\Connection\Hostmask - Provides a fluent interface.
	 */
	public function setHost($host)
	{
		$this->host = (string) $host;
		return $this;
	}

	/**
	 * Get the username for the current hostmask.
	 * @return string - The current username for the hostmask.
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * Set the username for the current hostmask
	 * @param string $username - The host to set.
	 * @return \Yukari\Connection\Hostmask - Provides a fluent interface.
	 */
	public function setUsername($username)
	{
		$this->username = (string) $username;
		return $this;
	}

	/**
	 * Get the nick for the current hostmask.
	 * @return string - The current nick for the hostmask.
	 */
	public function getNick()
	{
		return $this->nick;
	}

	/**
	 * Set the nick for the current hostmask
	 * @param string $nick - The nick to set.
	 * @return \Yukari\Connection\Hostmask - Provides a fluent interface.
	 */
	public function setNick($nick)
	{
		$this->nick = (string) $nick;
		return $this;
	}

	/**
	 * Parses a string containing the entire hostmask into a new instance of this class.
	 * @param string $hostmask - Entire hostmask including the nick, username, and host components
	 * @return \Yukari\Connection\Hostmask - New object instance populated with the data parsed from the provided hostmask string
	 *
	 * @throws \LogicException
	 */
	public static function load($hostmask)
	{
		if(!preg_match('/^([^!@]+)!(?:[ni]=)?([^@]+)@([^ ]+)/', $hostmask, $match))
		{
			throw new \LogicException(sprintf('Invalid hostmask "%s" specified', $hostmask));
		}

		list(, $nick, $username, $host) = $match;
		$self = static::newInstance();
		$self->setHost($host)->setUsername($username)->setNick($nick);
		return $self;
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
