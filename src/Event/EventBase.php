<?php
/**
 *
 *===================================================================
 *
 *  Failnet -- PHP-based IRC Bot
 *-------------------------------------------------------------------
 * @version     3.0.0 DEV
 * @category    Failnet
 * @package     event
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

namespace Failnet\Event;

/**
 * Failnet - Event base class,
 * 	    Base class that all events must extend.
 *
 *
 * @category    Failnet
 * @package     event
 * @author      Damian Bushong
 * @license     MIT License
 * @link        http://github.com/Obsidian1510/Failnet3
 */
abstract class EventBase implements \ArrayAccess
{
	/**
	 * @var array - The map of the various arguments for the event
	 */
	protected $map = array();

	/**
	 * Grabs this event object's event type
	 * @return string - The current event's type (omitting Failnet\Event\ of course)
	 */
	public function getType()
	{
		$class = get_class($this);
		return str_replace('Failnet\\Event\\', '', $class);
	}

	/**
	 * Sets the value of a certain arg to the specified value
	 * @param $arg_number - The arg number to set
	 * @param $arg_value - The value of the arg to set
	 * @return boolean - True if successful, false if no such arg to set.
	 */
	public function setArgNumber($arg_number, $arg_value)
	{
		if(!isset($this->map[$arg_number]))
			return false;
		$arg_key = 'arg_' . $this->map[$arg_number];
		$this->$arg_key = $arg_value;
		return true;
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
		$arg = "arg_$offset";
		return property_exists($this, $arg);
	}

	/**
	 * Get an "array" offset for this object.
	 * @param mixed $offset - The offset to grab from.
	 * @return mixed - The value of the offset, or null if the offset does not exist.
	 */
	public function offsetGet($offset)
	{
		$arg = "arg_$offset";
		return property_exists($this, $arg) ? $this->$arg : NULL;
	}

	/**
	 * Set an "array" offset to a certain value, if the offset exists
	 * @param mixed $offset - The offset to set.
	 * @param mixed $value - The value to set to the offset.
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$arg = "arg_$offset";
		if(property_exists($this, $arg))
			$this->$arg = $value;
	}

	/**
	 * Unset an "array" offset.
	 * @param mixed $offset - The offset to clear out.
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		$arg = "arg_$offset";
		$this->$arg = NULL;
	}
}
