<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     session
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

namespace Failnet\Session;
use Failnet as Root;
use Failnet\Bot as Bot;
use Failnet\Lib as Lib;

/**
 * Failnet - Session base class,
 *      Defines common methods and properties for session objects to use.
 *
 *
 * @category    Failnet
 * @package     session
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
class SessionBase
{
	/**
	 * @var Failnet\ACL\ACLBase - The ACL object that accompanies this session
	 */
	public $acl;

	/**
	 * @var Failnet\Lib\Hostmask - The hostmask that this session object represents
	 */
	protected $hostmask;

	/**
	 * @var integer - The timestamp that the session was last active
	 */
	protected $last_active = 0;

	/**
	 * @var array - Array of various snips of data related to this session to store while the session is active
	 */
	public $data = array();

	/**
	 * @var array - Array of bits of data that can be retrieved once and only once
	 */
	private $flash = array();

	/**
	 * Constructor
	 * @param Failnet\Lib\Hostmask $hostmask - The hostmask that this session object represents
	 * @param string $session_key - The session key assigned to this session
	 * @param string $pointer - The pointer that references this session in the core auth object
	 * @return void
	 */
	public function __construct(Lib\Hostmask $hostmask, $session_id, $pointer)
	{
		$this->hostmask = $hostmask;
		$this->data = array_merge($this->data, array('session_id' => $session_id, 'pointer'=> $pointer));
	}

	/**
	 * Grab the hostmask that this session object is for
	 * @return Failnet\Lib\Hostmask - The hostmask this session is assigned to.
	 */
	public function getHostmask()
	{
		return $this->hostmask;
	}

	/**
	 * Obtain or set a "flash" data index, or a value that can be retrieved once and only once
	 * @param string $flash_key - The key to use for the "flashed" data
	 * @param mixed $flash_value - The data to "flash" onto the session. If null, this method will attempt to return stored "flash" data if present instead, and then destroy it.
	 * @return mixed - If $flash_value is NULL, return is mixed (the "flashed" data that was stored or NULL if nothing present), or it will return void.
	 *
	 * @note: this method is declared final for a reason!
	 */
	final public function flash($flash_key, $flash_value = NULL)
	{
		if(is_null($flash_value))
		{
			if(isset($this->flash[$flash_key]))
			{
				$flash = $this->flash[$flash_key];
				// we've grabbed the data, and now we have to wipe it from the session object
				unset($this->flash[$flash_key]);
				return $flash;
			}
			return NULL;
		}
		else
		{
			$this->flash[$flash_key] = $flash_value;
		}
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
		return isset($this->data[$offset]);
	}

	/**
	 * Get an "array" offset for this object.
	 * @param mixed $offset - The offset to grab from.
	 * @return mixed - The value of the offset, or null if the offset does not exist.
	 */
	public function offsetGet($offset)
	{
		return isset($this->data[$offset]) ? $this->data[$offset] : NULL;
	}

	/**
	 * Set an "array" offset to a certain value, if the offset exists
	 * @param mixed $offset - The offset to set.
	 * @param mixed $value - The value to set to the offset.
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->data[$offset] = $value;
	}

	/**
	 * Unset an "array" offset.
	 * @param mixed $offset - The offset to clear out.
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}
}
