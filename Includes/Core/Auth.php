<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     core
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
 */

namespace Failnet\Core;
use Failnet as Root;
use Failnet\Bot as Bot;
use Failnet\Lib as Lib;

/**
 * Failnet - Auth object,
 *      Manages user sessions within Failnet.
 *
 *
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Auth extends Root\Hookable implements \Iterator, \ArrayAccess
{
	/**
	 * @var array - Array containing pointers to user session objects, allows session keys to be used with usernames
	 */
	protected $pointers = array();

	/**
	 * @var array - Array containing user session objects
	 */
	protected $sessions = array();

	/**
	 * Constructor
	 * @return void
	 */
	public function __construct()
	{
		// Build the session key salt
		$this->buildSessionSalt();
	}

	/**
	 * Builds and defines the session key salt if it has not been defined already
	 * @return void
	 */
	public function buildSessionSalt()
	{
		if(!defined('Failnet\SESSION_SALT'))
			define('Failnet\SESSION_SALT', hash('sha256', uniqid() . Failnet\BASE_MEMORY . ':' . Failnet\BASE_MEMORY_PEAK));
	}

	/**
	 * Create a new session for the specified hostmask
	 * @param Failnet\Lib\Hostmask $hostmask - The hostmask to create the session for.
	 * @return Failnet\User\Session - The new session we wanted.
	 *
	 * @throws Failnet\Core\AuthException
	 */
	public function newSession(Failnet\Lib\Hostmask $hostmask)
	{
		// Build a pointer to use for this session
		$pointer = $this->getPointer($hostmask);
		$session_key = hash('sha512', Failnet\SESSION_SALT . ':' . $hostmask['nick'] . ':' . time());
		$this->pointers[$pointer] = $session_key;

		// @todo link to pointer within session object

		$session = new Failnet\User\Session($hostmask);
		$this->sessions[$session_key] = $session;

		if(!$session instanceof Failnet\User\SessionInterface)
			throw new AuthException(); // @todo exception

		return $session;
	}

	/**
	 * Get a session object by hostmask
	 * @param Failnet\Lib\Hostmask $hostmask - The hostmask to get the session object for.
	 * @param boolean $new_session - If no session exists, do we want to create a new one?
	 * @return mixed - The desired session (Failnet\User\Session) or false if $new_session is false and there is no session.
	 */
	public function getSession(Failnet\Lib\Hostmask $hostmask, $new_session = true)
	{
		$session_key = $this->getSessionKey($hostmask);
		if($session_key === false || !isset($this->sessions[$session_key]))
		{
			if($new_session)
				return $this->newSession($hostmask);
			return false;
		}
		return $this->sessions[$session_key];
	}

	public function deleteSession(Failnet\Lib\Hostmask $hostmask)
	{
		// Allow the session to flush changes back to the DB.
		$this->sessions[$this->getSessionKey($hostmask)]->onDestroy();
		unset($this->sessions[$this->getSessionKey($hostmask)], $this->pointers[$this->getPointer($hostmask)]);
	}

	public function getSessionKey(Failnet\Lib\Hostmask $hostmask)
	{
		if(!isset($this->pointers[$this->getPointer($hostmask)]))
			return false;
		return $this->pointers[$this->getPointer($hostmask)];
	}

	public function getPointer(Failnet\Lib\Hostmask $hostmask)
	{
		return hash('md5', $hostmask['nick'] . ':' . $hostmask['username'] . ':' . $hostmask['host']);
	}

	/**
	 * Iterator methods
	 */

	/**
	 * Iterator method, rewinds the array back to the first element.
	 * @return void
	 */
	public function rewind()
	{
		return reset($this->sessions);
	}

	/**
	 * Iterator method, returns the key of the current element
	 * @return scalar - The key of the current element.
	 */
	public function key()
	{
		return key($this->sessions);
	}

	/**
	 * Iterator method, checks to see if the current position is valid.
	 * @return boolean - Whether or not the current array position is valid.
	 */
	public function valid()
	{
		return (!is_null(key($this->sessions)));
	}

	/**
	 * Iterator method, gets the current element
	 * @return Failnet\Lib\UserInterface - The current session of focus.
	 */
	public function current()
	{
		return current($this->sessions);
	}

	/**
	 * Iterator method, moves to the next session available.
	 * @return void
	 */
	public function next()
	{
		next($this->sessions);
	}

	/**
	 * ArrayAccess methods
	 */

	/**
	 * Check if an "array" offset exists in this object.
	 * @param mixed $offset - The offset to check.
	 * @return boolean - Does anything exist for this offset?
	 */
	public function offsetExists($offset)
	{
		return isset($this->sessions[$offset]);
	}

	/**
	 * Get an "array" offset for this object.
	 * @param mixed $offset - The offset to grab from.
	 * @return mixed - The value of the offset, or null if the offset does not exist.
	 */
	public function offsetGet($offset)
	{
		return isset($this->sessions[$offset]) ? $this->sessions[$offset] : NULL;
	}

	/**
	 * Set an "array" offset to a certain value, if the offset exists
	 * @param mixed $offset - The offset to set.
	 * @param mixed $value - The value to set to the offset.
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->sessions[$offset] = $value;
	}

	/**
	 * Unset an "array" offset.
	 * @param mixed $offset - The offset to clear out.
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		// Allow the session to flush changes back to the DB.
		$this->sessions[$offset]->onDestroy();
		unset($this->sessions[$offset]);
	}
}
