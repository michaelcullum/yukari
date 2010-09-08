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
 * Failnet - Core class,
 *      Failnet in a nutshell.  Faster, smarter, better, and with a sexier voice.
 *
 *
 * @category    Failnet
 * @package     core
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet-PHP-IRC-Bot
 */
class Auth extends Root\Hookable implements Iterator, ArrayAccess
{
	/**
	 * @var array - Array containing pointers to user session objects
	 */
	protected $pointers = array();

	/**
	 * @var array - Array containing user session objects
	 */
	protected $sessions = array();

	/**
	 * @var string - The class that will be used to manage user sessions (this must implement Failnet\Lib\UserInterface!)
	 */
	protected $user_object = '';

	/**
	 * Constructor
	 * @return void
	 */
	public function __construct()
	{
		// Store the name of the user object that we'll be using for the user class
		$this->user_object = Bot::getOption('auth.user_object', 'Failnet\\Lib\\User');

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

	public function newSession(Failnet\Lib\Hostmask $hostmask)
	{
		// Build a pointer to use for this session
		$pointer = $this->getPointer($hostmask);
		$session_key = hash('sha512', Failnet\SESSION_SALT . ':' . $hostmask['nick'] . ':' . time());
		$this->pointers[$pointer] = $session_key;

		// Workaround for derp php
		$user_object = $this->user_object;
		$session = new $user_object($hostmask);
		$this->sessions[$session_key] = $session;

		if(!$session instanceof Failnet\Lib\UserInterface)
			throw new AuthException(); // @todo exception

		return $session;
	}

	public function getSession(Failnet\Lib\Hostmask $hostmask)
	{
		return $this->sessions[$this->getSessionKey($hostmask)];
	}

	public function deleteSession(Failnet\Lib\Hostmask $hostmask)
	{
		unset($this->sessions[$this->getSessionKey($hostmask)], $this->pointers[$this->getPointer($hostmask)]);
	}

	protected function getSessionKey(Failnet\Lib\Hostmask $hostmask)
	{
		return $this->pointers[$this->getPointer($hostmask)];
	}

	protected function getPointer(Failnet\Lib\Hostmask $hostmask)
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
}
