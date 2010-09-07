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
class Auth extends Root\Hookable implements Iterator
{
	protected $pointers = array();

	/**
	 * @var array - Array of users logged in
	 */
	protected $sessions = array();

	/**
	 * @var string - The class that will be used to manage user sessions (this must implement Failnet\Lib\UserInterface!)
	 */
	protected $user_object = '';

	public function __construct()
	{
		$this->user_object = Bot::getOption('auth.user_object', 'Failnet\\Lib\\User');

		// @todo move to session instantiation code
		//if(!$this->user_object instanceof Failnet\Lib\UserInterface))
			//throw new AuthException(); // @todo exception
	}

	public function newSession()
	{
		// asdf
	}

	public function getSession($session_number)
	{
		// asdf
	}

	public function deleteSession($session_number)
	{
		// asdf
	}

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
}
