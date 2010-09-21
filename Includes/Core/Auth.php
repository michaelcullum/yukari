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
 * @link        http://github.com/Obsidian1510/Failnet3
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
use Failnet\Core\Session as Session;

/**
 * Failnet - Auth object,
 *      Manages user sessions within Failnet.
 *
 *
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
class Auth implements \Iterator, \ArrayAccess
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
	public function newSession(Lib\Hostmask $hostmask)
	{
		// Build a pointer to use for this session
		$pointer = $this->getPointer($hostmask);
		$session_id = hash('sha512', Failnet\SESSION_SALT . ':' . $hostmask['nick'] . ':' . time());
		$this->pointers[$pointer] = $session_id;

		$session = new Session\Standard($hostmask, $session_id, $pointer);
		$this->sessions[$session_id] = $session;

		if(!$session instanceof Session\SessionBase)
			throw new AuthException('Session object does not extend session base class', AuthException::ERR_AUTH_SESSION_NOT_SESSIONBASE_CHILD);

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
		$session_id = $this->getSessionID($hostmask);
		if($session_id === false || !isset($this->sessions[$session_id]))
		{
			if($new_session)
				return $this->newSession($hostmask);
			return false;
		}
		return $this->sessions[$session_id];
	}

	/**
	 * Deletes the session registered to a specific hostmask
	 * @param Failnet\Lib\Hostmask $hostmask - The hostmask to delete the session for.
	 * @return void
	 */
	public function deleteSession(Failnet\Lib\Hostmask $hostmask)
	{
		$session_id = $this->getSessionID($hostmask);
		if($session_id === false)
			return;

		// Allow the session to flush changes, last seen info, etc. first.
		$this->sessions[$session_id]->onDestroy();
		unset($this->sessions[$session_id], $this->pointers[$this->getPointer($hostmask)]);
	}

	/**
	 * Get the session ID for a specific hostmask
	 * @param Failnet\Lib\Hostmask $hostmask - The hostmask to grab the session ID for.
	 * @return string - The session ID for the sessions specified.
	 */
	public function getSessionID(Lib\Hostmask $hostmask)
	{
		if(!isset($this->pointers[$this->getPointer($hostmask)]))
			return false;
		return $this->pointers[$this->getPointer($hostmask)];
	}

	/**
	 * Gets the pointer string used to refer to a specific session based on hostmask data
	 * @param Failnet\Lib\Hostmask $hostmask - The hostmask to obtain the pointer for
	 * @return string - The pointer string for the specific session
	 */
	public function getPointer(Lib\Hostmask $hostmask)
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
		if(!isset($this->sessions[$offset]))
			return;

		// Allow the session to flush changes back to the DB.
		$this->sessions[$offset]->onDestroy();
		unset($this->pointers[$this->sessions[$offset]->pointer], $this->sessions[$offset]);
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
 * @note reserves 206xx error codes
 */
class AuthException extends Root\FailnetException
{
	const ERR_AUTH_SESSION_NOT_SESSIONBASE_CHILD = 20600;
}
